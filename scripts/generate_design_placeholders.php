<?php

/**
 * Generate product placeholders matching AANI Market design system
 */

$storagePath = __DIR__ . '/../storage/app/public/products';

if (!is_dir($storagePath)) {
    mkdir($storagePath, 0755, true);
}

// Color palette from welcome.blade.php
$colors = [
    'vegetables' => ['primary' => '#1D6F42', 'light' => '#EAF4EE', 'icon' => '🥬'],
    'fruits' => ['primary' => '#D97706', 'light' => '#FEF3C7', 'icon' => '🍎'],
    'dairy' => ['primary' => '#7A7871', 'light' => '#F5F4F0', 'icon' => '🥛'],
    'meat' => ['primary' => '#1A1916', 'light' => '#E4E2DC', 'icon' => '🥩'],
];

// Create SVG placeholders for each category
foreach ($colors as $category => $palette) {
    $filename = "placeholder-{$category}.svg";
    $filepath = $storagePath . '/' . $filename;
    
    $svg = <<<SVG
<svg width="400" height="400" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
  <!-- Background -->
  <rect width="400" height="400" fill="{$palette['light']}"/>
  
  <!-- Accent bar -->
  <rect width="400" height="8" fill="{$palette['primary']}"/>
  
  <!-- Center circle with text -->
  <circle cx="200" cy="200" r="80" fill="{$palette['primary']}" opacity="0.1"/>
  <circle cx="200" cy="200" r="70" fill="none" stroke="{$palette['primary']}" stroke-width="2" opacity="0.3"/>
  
  <!-- Category label -->
  <text x="200" y="195" font-family="'DM Sans', sans-serif" font-size="28" font-weight="600" text-anchor="middle" fill="{$palette['primary']}">
    {$palette['icon']}
  </text>
  
  <text x="200" y="250" font-family="'DM Sans', sans-serif" font-size="18" font-weight="500" text-anchor="middle" fill="#1A1916">
    {$category}
  </text>
  
  <text x="200" y="275" font-family="'DM Sans', sans-serif" font-size="12" text-anchor="middle" fill="#7A7871">
    AANI Market
  </text>
</svg>
SVG;
    
    file_put_contents($filepath, $svg);
    echo "✓ Created: {$filename}\n";
}

// Create generic product placeholder
$genericSvg = <<<SVG
<svg width="400" height="400" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
  <!-- Background -->
  <rect width="400" height="400" fill="#F5F4F0"/>
  
  <!-- Accent bar -->
  <rect width="400" height="8" fill="#1D6F42"/>
  
  <!-- Center circle -->
  <circle cx="200" cy="200" r="80" fill="#1D6F42" opacity="0.1"/>
  <circle cx="200" cy="200" r="70" fill="none" stroke="#1D6F42" stroke-width="2" opacity="0.3"/>
  
  <!-- Product icon -->
  <text x="200" y="200" font-family="'DM Sans', sans-serif" font-size="48" font-weight="600" text-anchor="middle" dominant-baseline="central" fill="#1D6F42">
    📦
  </text>
  
  <!-- Text -->
  <text x="200" y="270" font-family="'DM Sans', sans-serif" font-size="18" font-weight="500" text-anchor="middle" fill="#1A1916">
    Product Image
  </text>
  
  <text x="200" y="295" font-family="'DM Sans', sans-serif" font-size="12" text-anchor="middle" fill="#7A7871">
    AANI Market
  </text>
</svg>
SVG;

file_put_contents($storagePath . '/product-placeholder.svg', $genericSvg);
echo "✓ Created: product-placeholder.svg\n";

echo "\n✅ All design-matched placeholders created!\n";
