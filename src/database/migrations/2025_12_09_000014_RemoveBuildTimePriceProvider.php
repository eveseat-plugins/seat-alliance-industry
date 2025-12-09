<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        DB::table("price_provider_instances")
            ->where("backend","recursivetree/seat-alliance-industry/build-time")
            ->delete();
    }

    public function down()
    {

    }
};

