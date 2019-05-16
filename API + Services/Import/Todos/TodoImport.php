<?php

namespace App\import\Todos;

use App\Todos\Todo;
use App\Import\ImportInterface;
use App\Import\ValidateImportDataTrait;

class TodoImport implements ImportInterface
{
    use ValidateImportDataTrait;

    public function import($todos)
    {
        $result = $this->validate('todo', $todos);

        if (!empty($result['errors'])) {
            return response()->json(['success' => $result['success'], 'error' => $result['errors']], 200);
        } else {
            foreach ($todos as $todo) {
                $todo['uuid'] = generateUuid();
                $todo['user_id'] = auth()->user()->id;
                Todo::create($todo);
            }

            return response()->json(['success' => 'Import Todo Data successfully'], 200);
        }
    }
}
