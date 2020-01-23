<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteInstantSearch\Plugin\FrontController;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;
use Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product\ThumbnailHelper;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Fast Dispatch plugin for instant-search.
 * Bypass the routers_match procedure to dispatch directly to the proper controller.
 *
 * @category Smile
 * @package  Smile\ElasticSuiteInstantSearch
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DispatchPlugin
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var \Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product\ThumbnailHelper
     */
    private $thumbnailHelper;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * DispatchPlugin constructor.
     *
     * @param \Magento\Framework\App\ActionFactory $actionFactory   Action Factory
     * @param ResponseInterface                    $response        The response
     * @param ThumbnailHelper                      $thumbnailHelper Thumbnail Helper
     * @param RedirectFactory                      $redirectFactory Redirect Factory
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        ResponseInterface $response,
        ThumbnailHelper $thumbnailHelper,
        RedirectFactory $redirectFactory
    ) {
        $this->actionFactory   = $actionFactory;
        $this->response        = $response;
        $this->thumbnailHelper = $thumbnailHelper;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject Front Controller
     * @param \Closure                                        $proceed The dispatch() method of front controller
     * @param \Magento\Framework\App\RequestInterface         $request HTTP Request
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\Response\Http HTTP Response
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        try {
            if ($this->matchInstantSearchRequest($request)) {
                $this->response->setNoCacheHeaders();
                $action = $this->actionFactory->create(\Magento\Search\Controller\Ajax\Suggest::class);

                return $action->execute();
            } elseif ($this->matchThumbnailRequest($request)) {
                $thumbnailUrl = $this->thumbnailHelper->getImageUrl($request->getParam('productId', null));
                if ($thumbnailUrl !== '') {
                    $this->response->setPrivateHeaders(3600);

                    return $this->redirectFactory->create()->setUrl($thumbnailUrl);
                }
            }
        } catch (\Exception $exception) {
            ;
        }

        return $proceed($request);
    }

    /**
     * Check if this is an instant search request.
     *
     * @param \Magento\Framework\App\RequestInterface $request The request
     *
     * @return bool
     */
    private function matchInstantSearchRequest($request)
    {
        $requestPath = trim($request->getPathInfo(), '/');

        return $requestPath === 'search/ajax/suggest';
    }

    /**
     * Check if this is an instant search request.
     *
     * @param \Magento\Framework\App\RequestInterface $request The request
     *
     * @return bool
     */
    private function matchThumbnailRequest($request)
    {
        $requestPath = trim($request->getPathInfo(), '/');

        return $requestPath === 'instantsearch/ajax/thumbnail';
    }
}
