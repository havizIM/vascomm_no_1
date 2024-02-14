<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class GenerateCode
{
  public static function productCode($prefix = null)
  {
    $initial = 'PRD-'.date('ymd').'-';

    if ($prefix) {
      $initial = 'PRD-' . date('ymd') . '-' . $prefix . '-';
    }

    $q = DB::table('products')->select(DB::raw('MAX(RIGHT(code, 4)) as kd_max'))->where('code', 'like', '%' . $initial . '%');

    if ($q->count() > 0) {
      foreach ($q->get() as $k) {
        $tmp = (int) substr($k->kd_max, -3, 3);
        $no = $tmp + 1;
        $kd = $initial . sprintf("%04s", $no);
      }
    } else {
      $kd = $initial . "0001";
    }

    return $kd;
  }
}
