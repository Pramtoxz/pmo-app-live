<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\PartCategoryImage;

class PartHelper
{
    const DEFAULT_IMAGE = 'https://pmo.menara-agung.com/assets/images/lg_honda.jpg';

    public static function getPartImage($kodePart, $product = null, $part = null)
    {
        if ($product && $product->gambar) {
            return url('images/part/' . $product->gambar);
        }

        if (!$product) {
            $product = Product::where('kode_part', $kodePart)->first();
            if ($product && $product->gambar) {
                return url('images/part/' . $product->gambar);
            }
        }

        if ($part && $part->fk_detail_sub_kelompok_part) {
            $categoryImage = PartCategoryImage::where('kode_kelompok', $part->fk_detail_sub_kelompok_part)->first();
            if ($categoryImage && $categoryImage->gambar) {
                return url('images/category/' . $categoryImage->gambar);
            }
        }

        return self::DEFAULT_IMAGE;
    }

    public static function getPartName($part, $product = null)
    {
        if ($product && $product->nama) {
            return $product->nama;
        }

        return $part->nm_part ?? '-';
    }

   
    public static function getPartDescription($part, $product = null)
    {
        if ($product && $product->deskripsi) {
            return $product->deskripsi;
        }

        return self::getPartName($part, $product);
    }
}
