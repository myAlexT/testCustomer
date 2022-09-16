<?php

declare(strict_types=1);

namespace Test\Customer\CustomerData;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CustomerStatus
 * @package Test\Customer\CustomerData
 */
class CustomerStatus implements SectionSourceInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * CustomerStatus constructor.
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return array
     */
    public function getSectionData(): array
    {
        return [
            'status' => $this->getCustomerStatus(),
        ];
    }

    /**
     * @return string
     */
    public function getCustomerStatus(): string
    {
        $currentCustomerId = $this->session->getCustomerId();
        $status = '';
        if ($currentCustomerId) {
            try {
                $customer = $this->customerRepository->getById($currentCustomerId);
                $customAttribute = $customer->getCustomAttribute('customer_status');
                if ($customAttribute) {
                    $status = $customAttribute->getValue();
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t get CustomerStatus.'));
            }
        }

        return $status;
    }
}
