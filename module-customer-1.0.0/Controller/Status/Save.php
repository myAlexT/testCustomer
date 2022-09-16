<?php

declare(strict_types=1);

namespace Test\Customer\Controller\Status;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Class Save
 * @package Test\Customer\Controller\Status
 */
class Save implements HttpPostActionInterface
{
    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Save constructor.
     * @param RedirectFactory $resultRedirectFactory
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        RedirectFactory $resultRedirectFactory,
        Session $customerSession,
        Validator $formKeyValidator,
        ManagerInterface $messageManager,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->session = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $validFormKey = $this->formKeyValidator->validate($this->request);
        if ($validFormKey && $this->request->isPost()) {
            try {
                $currentCustomerId = $this->session->getCustomerId();
                $customer = $this->customerRepository->getById($currentCustomerId);
                $customer->setCustomAttribute(
                    'customer_status',
                    $this->request->getParam('customer_status')
                );
                $this->customerRepository->save($customer);
                $this->messageManager->addSuccessMessage(__('You saved the customer status.'));
            } catch (InputMismatchException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('test/status');
    }

}
