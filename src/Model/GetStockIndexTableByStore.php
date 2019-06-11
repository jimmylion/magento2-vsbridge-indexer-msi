<?php
/**
 * @package  Divante\VsbridgeIndexerMsi
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerMsi\Model;

use Divante\VsbridgeIndexerMsi\Api\GetStockIdBySalesChannelCodeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Class GetStockIndexTableByStore
 */
class GetStockIndexTableByStore
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var GetStockIdBySalesChannelCodeInterface
     */
    private $getStockIdByCode;

    /**
     * @var array
     */
    private $cacheStockId = [];

    /**
     * GetStockIndexTableByStore constructor.
     *
     * @param GetStockIdBySalesChannelCodeInterface $stockIdBySalesChannel
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GetStockIdBySalesChannelCodeInterface $stockIdBySalesChannel,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->getStockIdByCode = $stockIdBySalesChannel;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $storeId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $storeId): string
    {
        if (!isset($this->cacheStockId[$storeId])) {
            $stockId = $this->getStockId($storeId);
            $this->cacheStockId[$storeId] = $this->stockIndexTableNameResolver->execute($stockId);
        }

        return $this->cacheStockId[$storeId];
    }

    /**
     * @param int $storeId
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStockId(int $storeId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $website = $this->storeManager->getWebsite($websiteId);

        return $this->getStockIdByCode->execute($website->getCode());
    }
}
