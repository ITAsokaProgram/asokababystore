<?php
function build_pagination_url($new_offset) {
    $params = $_GET;
    $params['offset'] = $new_offset;

    $is_searching = !empty($params['search']);

    if (($new_offset === 0 || $new_offset === '0') && !$is_searching) {
        unset($params['offset']);
    }
    
    return '?' . http_build_query($params);
}

function getPriceRange($models) {
    if (empty($models)) return null;
    $prices = array_column(array_column($models, 'price_info'), 0);
    $original_prices = array_column($prices, 'original_price');
    if (empty($original_prices)) return null;
    
    $minPrice = min($original_prices);
    $maxPrice = max($original_prices);
    
    return ($minPrice == $maxPrice)
        ? number_format($minPrice, 0, ',', '.')
        : number_format($minPrice, 0, ',', '.') . ' - ' . number_format($maxPrice, 0, ',', '.');
}
?>