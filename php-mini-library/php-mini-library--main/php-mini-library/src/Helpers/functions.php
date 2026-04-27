<?php

// --- Formatting Helpers ---

function formatProductName(string $title): string 
{
    return ucwords(strtolower($title));
}

function formatPrice(float $price): string 
{
    return '$' . number_format($price, 2);
}

// --- Inventory & Stock Logic ---

function getStockStatus(int $quantity): array 
{
    if ($quantity <= 0) {
        return ['text' => 'Out of stock', 'color' => 'danger'];
    } elseif ($quantity <= 5) {
        return ['text' => 'Low stock', 'color' => 'warning text-dark'];
    }
    return ['text' => 'In stock', 'color' => 'success'];
}

function getTotalQuantity(array $products): int 
{
    return array_reduce($products, fn($carry, $item) => $carry + $item['quantity'], 0);
}

function getAvailableProducts(array $products): array 
{
    return array_values(array_filter($products, fn($item) => $item['quantity'] > 0));
}

?>