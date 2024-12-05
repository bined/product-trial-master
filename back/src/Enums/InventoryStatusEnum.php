<?php

namespace App\Enums;

Enum InventoryStatusEnum: string
{
    case in_stock = "INSTOCK";
    case low_stock = "LOWSTOCK";
    case out_of_stock = "OUTOFSTOCK";

    public static function getInventoryStatusValues(): array
    {
        return array_map(fn(self $status) => $status->value, self::cases());
    }
}