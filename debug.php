$products = \App\Models\Product::orderBy('id', 'DESC')->limit(10)->get();
foreach ($products as $p) {
  echo "ID: {$p->id} | {$p->product_name} | Image: " . ($p->product_image_url ?? 'NULL') . PHP_EOL;
}
