<?php

namespace App\Traits;

trait CompareSourceAndUpdate
{
    use UpdateEsIndex;

    /**
     * Compare each data source.
     *
     * @param array  $incomingData
     * @param object $dbData
     *
     * @return object
     */
    public function compareSourceAndUpdate(array $incomingData, $dbData)
    {
        $oldRecord = clone $dbData;

        foreach (array_except($incomingData, $this->getExcludeColumns()) as $key => $item) {
            if (config('ms_specific.source_priority.' . $key . '.' . $dbData->$key['source']) <= config('ms_specific.source_priority.' . $key . '.' . $item['source'])) {
                $dbData = $this->updateResource($dbData, $key, $item, $incomingData['source']);
            }
        }

        if ($dbData->getChanges()) {
            $this->updateEsResource($oldRecord, $dbData);
            $dbData->histories()->delete();
            $dbData->histories()->create($dbData->toArray());
        }

        return $dbData;
    }

    /**
     * Update resource record.
     *
     * @param object $resource
     * @param string $key
     * @param array  $item
     * @param string $source
     *
     * @return object
     */
    public function updateResource($resource, $key, $item, $source): object
    {
        if ($item['updated_at'] >= $resource[$key]['updated_at']) {
            unset($resource['threshold']);
            $resource->fill([
                $key => [
                    'value'      => $item['value'],
                    'source'     => $item['source'],
                    'updated_at' => $item['updated_at'],
                ],
                'source' => $source,
            ]);

            $resource->save();
        }

        return $resource;
    }

    /**
     * Create Or Update Custom Questions.
     *
     * @param array  $data
     * @param mixed  $questionModel
     * @param object $model
     */
    public function createOrUpdateCustomQuestions(array $data, $questionModel, $model): void
    {
        if (array_has($data, 'custom_questions')) {
            $dbCustomQuestions = $questionModel->mapWithKeys(function ($item) {
                return [$item->question['question'] => $item];
            })->all();
            //update or create custom question.
            collect($data['custom_questions'])->except('source')->filter(function ($question) use ($dbCustomQuestions,$model) {
                $questionString = $question['question']['question'];
                if (array_key_exists($questionString, $dbCustomQuestions)) {
                    if (!empty($this->checkQuestionValuesDiff($questionString, $dbCustomQuestions, $question))) {
                        //update custom question.
                        $dbCustomQuestions[$questionString]->fill($question)->save();

                        //create record and delete old record from history table.
                        $dbCustomQuestions[$questionString]->histories()->delete();
                        $dbCustomQuestions[$questionString]->histories()->create($dbCustomQuestions[$questionString]->toArray());
                    }
                } else {
                    $this->createCustomQuestions($question, $model);
                }
            });
        }
    }

    public function checkQuestionValuesDiff($questionString, $dbCustomQuestions, $question)
    {
        return array_merge(array_diff($question['values'], $dbCustomQuestions[$questionString]->values),
                array_diff($dbCustomQuestions[$questionString]->values, $question['values']));
    }
}
