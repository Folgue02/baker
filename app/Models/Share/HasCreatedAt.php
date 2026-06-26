<?php
namespace App\Models\Share;

use DateTime;

trait HasCreatedAt
{
    public ?string $createdAt;

    public function setCreatedAt(?string $createdAt)
    {
        if (!$createdAt)
            $this->createdAt = null;

        $timestampFormat = config('baker.timestamp_format');

        $dtCreatedAt = DateTime::createFromFormat($timestampFormat, $createdAt);

        if (!$dtCreatedAt)
            throw new \RuntimeException("An attempt to set createdAt was made for class " . self::class . ", but the given string didn't follow the proper timestamp format ($createdAt)");

        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt ? $this->createdAt : null;
    }

    public function createdAtAsDateTime(): ?DateTime
    {
        return DateTime::createFromFormat(config('baker.timestamp_format'), $this->createdAt);
    }
}
