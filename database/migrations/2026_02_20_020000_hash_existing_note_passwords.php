<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HashExistingNotePasswords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('notes')
            ->select('id', 'password')
            ->whereNotNull('password')
            ->orderBy('id')
            ->chunkById(100, function ($notes) {
                foreach ($notes as $note) {
                    if (!$this->isHashed((string) $note->password)) {
                        DB::table('notes')
                            ->where('id', $note->id)
                            ->update([
                                'password' => Hash::make((string) $note->password),
                            ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function isHashed(string $value): bool
    {
        $hashInfo = Hash::info($value);

        return ($hashInfo['algo'] ?? 0) !== 0;
    }
}
