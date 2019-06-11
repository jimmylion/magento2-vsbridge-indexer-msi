<?php
/**
 * @package   Divante\VsbridgeIndexerMsi
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerMsi\Model;

use Divante\VsbridgeIndexerMsi\Api\GetStockIdBySalesChannelCodeInterface;
use Magento\InventorySales\Model\ResourceModel\StockIdResolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Class GetStockIdBySalesChannelCode
 */
class GetStockIdBySalesChannelCode implements GetStockIdBySalesChannelCodeInterface
{
    /**
     * @var array
     */
    private $cacheStockIdByCode = [];

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    /**
     * @param StockIdResolver $stockIdResolver
     */
    public function __construct(StockIdResolver $stockIdResolver)
    {
        $this->stockIdResolver = $stockIdResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $code): int
    {
        if (!isset($this->cacheStockIdByCode[$code])) {
            $stockId = $this->stockIdResolver->resolve(
                SalesChannelInterface::TYPE_WEBSITE,
                $code
            );

            if (null === $stockId) {
                throw new NoSuchEntityException(__('No linked stock found'));
            }

            $this->cacheStockIdByCode[$code] = $stockId;
        }

        return $this->cacheStockIdByCode[$code];
    }
}
