<?php
/**
 * @category    ClassyLlama
 * @package
 * @copyright   Copyright (c) 2019 Classy Llama Studios, LLC
 */

namespace TurnTo\SocialCommerce\Controller\SSO;


use Magento\Framework\App\Action\Context;
use TurnTo\SocialCommerce\Helper\Config;

class RedirectToLogin extends \Magento\Framework\App\Action\Action
{


    /**
     * @var Magento\Framework\UrlInterface
     */
    private $uriInterface;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;


    public function __construct(Context $context,
                                \Magento\Framework\Message\ManagerInterface $messageManager,
                                \Magento\Framework\UrlInterface $uriInterface,
                                Config $config,
                                \Magento\Customer\Model\SessionFactory $customerSessionFactory
    )
    {
        $this->messageManager = $messageManager;
        $this->uriInterface = $uriInterface;
        $this->config = $config;
        $this->customerSessionFactory = $customerSessionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $url = $this->_redirect->getRefererUrl();

        $resultRedirect = $this->resultRedirectFactory->create();
        $login_url = $this->uriInterface
            ->getUrl('customer/account/login',
                ['referer' => base64_encode($url)]
            );
        $resultRedirect->setPath($login_url);
        $this->messageManager->addNoticeMessage($this->getMessage());

        //store the PDP in session
        $customerSession = $this->customerSessionFactory->create();
        $customerSession->setPdpUrl($url);
        return $resultRedirect;
    }

    public function getMessage()
    {
        $action = $this->getRequest()->getParam('action');
        switch ($action) {
            case "QUESTION_CREATE":
                if($this->getRequest()->getParam('authSetting') == 'ANONYMOUS'){
                    $message = $this->config->getQuestionMsgAnon();
                }
                $message = $this->config->getQuestionMsg();
                break;
            case "ANSWER_CREATE":
                $message = $this->config->getAnswerMessage();
                break;
            case "REVIEW_CREATE":
                if ($this->getRequest()->getParam('authSetting') == 'PURCHASE_REQUIRED') {
                    $message = $this->config->getReviewMsgPurchaseReq();
                    break;
                }
                $message = $this->config->getReviewMsg();
                break;
            case "REPLY_CREATE":
                $message = $this->config->getReplyMsg();
                break;
            default:
                $message = "";
        }

        return $message;
    }

}

