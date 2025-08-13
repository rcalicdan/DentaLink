<?php

namespace App\Enums;

enum InventoryCategory: string
{
    case CONSUMABLES = 'Consumables';
    case INSTRUMENTS = 'Instruments';
    case MATERIALS = 'Materials';
    case EQUIPMENT = 'Equipment';
}