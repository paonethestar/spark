<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLISTPAUSEDTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('LIST_PAUSED', function(Blueprint $table)
		{
			$table->string('APP_UID', 32)->default('');
			$table->integer('DEL_INDEX')->default(0);
			$table->string('USR_UID', 32)->default('')->index('indexPausedUser');
			$table->string('TAS_UID', 32)->default('');
			$table->string('PRO_UID', 32)->default('');
			$table->integer('APP_NUMBER')->default(0);
			$table->text('APP_TITLE', 16777215)->nullable();
			$table->text('APP_PRO_TITLE', 16777215)->nullable();
			$table->text('APP_TAS_TITLE', 16777215)->nullable();
			$table->dateTime('APP_PAUSED_DATE');
			$table->dateTime('APP_RESTART_DATE');
			$table->string('DEL_PREVIOUS_USR_UID', 32)->nullable()->default('');
			$table->string('DEL_PREVIOUS_USR_USERNAME', 100)->nullable()->default('');
			$table->string('DEL_PREVIOUS_USR_FIRSTNAME', 50)->nullable()->default('');
			$table->string('DEL_PREVIOUS_USR_LASTNAME', 50)->nullable()->default('');
			$table->string('DEL_CURRENT_USR_USERNAME', 100)->nullable()->default('');
			$table->string('DEL_CURRENT_USR_FIRSTNAME', 50)->nullable()->default('');
			$table->string('DEL_CURRENT_USR_LASTNAME', 50)->nullable()->default('');
			$table->dateTime('DEL_DELEGATE_DATE');
			$table->dateTime('DEL_INIT_DATE')->nullable();
			$table->dateTime('DEL_DUE_DATE')->nullable();
			$table->string('DEL_PRIORITY', 32)->default('3');
			$table->integer('PRO_ID')->nullable()->default(0)->index('INDEX_PRO_ID');
			$table->integer('USR_ID')->nullable()->default(0)->index('INDEX_USR_ID');
			$table->integer('TAS_ID')->nullable()->default(0)->index('INDEX_TAS_ID');
			$table->primary(['APP_UID','DEL_INDEX']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('LIST_PAUSED');
	}

}
