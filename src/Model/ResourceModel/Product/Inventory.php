<?php
/**
 * @package   Divante\VsbridgeIndexerMsi
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerMsi\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerMsi\Model\GetStockIndexTableByStore;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Indexer\IndexStructure;

/**
 * Class Inventory
 */
class Inventory
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var GetStockIndexTableByStore
     */
    private $getSockIndexTableByStore;

    /**
     * Inventory constructor.
     *
     * @param GetStockIndexTableByStore $getSockIndexTableByStore
     * @param ResourceConnection $resourceModel
     */
    public function __construct(
        GetStockIndexTableByStore $getSockIndexTableByStore,
        ResourceConnection $resourceModel
    ) {
        $this->getSockIndexTableByStore = $getSockIndexTableByStore;
        $this->resource = $resourceModel;
    }

    /**
     * @param int $storeId
     * @param array $skuList
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadInventory(int $storeId, array $skuList): array
    {
        return $this->getInventoryData($storeId, $skuList);
    }

    /**
     * @param int $storeId
     * @param array $skuList
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadChildrenInventory(int $storeId, array $skuList): array
    {
        return $this->getInventoryData($storeId, $skuList);
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getInventoryData(int $storeId, array $productIds): array
    {
        $connection = $this->resource->getConnection();
        $stockItemTableName = $this->getSockIndexTableByStore->execute($storeId);

        $select = $connection->select()
            ->from(
                $stockItemTableName,
                [
                    'sku' => IndexStructure::SKU,
                    'qty' => IndexStructure::QUANTITY,
                    'is_in_stock' => IndexStructure::IS_SALABLE,
                    'stock_status' => IndexStructure::IS_SALABLE,
                ]
            )
            ->where(IndexStructure::SKU . ' IN (?)', $productIds);

        return $connection->fetchAssoc($select);
    }
}
