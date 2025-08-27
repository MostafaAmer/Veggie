<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TransactionAction extends Enum
{
    const Created         = 'created';
    const Updated         = 'updated';
    const Approved        = 'approved';
    const Cancelled       = 'cancelled';
    const MetadataUpdated = 'metadata_updated';
    const StatusChanged   = 'status_changed';
}
