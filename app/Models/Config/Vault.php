<?php
namespace App\Models\Config;

final class Vault
{
    public string $name;
    public string $originRoot;
    public string $targetRoot;
    public ?Settings $settings = null;
}
