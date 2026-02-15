<?php

namespace App;

class Cart
{
    public $items = null;
    public $totalQty = 0;
    public $totalPrice = 0;

    public function __construct($oldCart)
    {
        if ($oldCart) {
            $this->items = $oldCart->items;
            $this->totalQty = $oldCart->totalQty;
            $this->totalPrice = $oldCart->totalPrice;
        }
    }

    public function add($item, $id, $quantity = 1)
    {
        $storedItem = ['qty' => 0, 'price' => $item->price_per_unit, 'item' => $item];
        if ($this->items) {
            if (array_key_exists($id, $this->items)) {
                $storedItem = $this->items[$id];
            }
        }
        $storedItem['qty'] += $quantity;
        $storedItem['price'] = $item->price_per_unit * $storedItem['qty'];
        $this->items[$id] = $storedItem;
        $this->totalQty += $quantity;
        $this->totalPrice += $item->price_per_unit * $quantity;
    }

    public function reduceByOne($id)
    {
        if ($this->items[$id]['qty'] > 1) {
            $this->items[$id]['qty']--;
            $this->items[$id]['price'] -= $this->items[$id]['item']->price_per_unit;
            $this->totalQty--;
            $this->totalPrice -= $this->items[$id]['item']->price_per_unit;
        } else {
            unset($this->items[$id]);
            $this->totalQty--;
            $this->totalPrice -= $this->items[$id]['item']->price_per_unit;
        }
    }

    public function removeItem($id)
    {
        if (array_key_exists($id, $this->items)) {
            $this->totalQty -= $this->items[$id]['qty'];
            $this->totalPrice -= $this->items[$id]['price'];
            unset($this->items[$id]);
        }
    }
}
