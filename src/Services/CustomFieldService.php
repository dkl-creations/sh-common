<?php

namespace DklCreations\SHCommon\Services;

use DklCreations\SHCommon\Models\CustomField;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CustomFieldService extends BaseService
{

    /**
     * create a record
     *
     * @param  array  $input
     * @return object
     */

    public function create($input)
    {
        // check for limit first
        $this->checkMaxLimit($input);

        $field_map = $this->getFieldMap();

        if (!isset($field_map[$input['type']]) && !$this->noSchemaChange($input['type'])) {
            fail('Invalid field type');
        }

        if (!empty($input['is_searchable']) && in_array($input['type'], ['textarea', 'html'])) {
            fail('Textarea/HTML fields cannot be searchable');
        }

        // determine next display order
        $last = CustomField::where('resource', $input['resource'])->orderBy('display_order', 'DESC')->first();
        $input['display_order'] = $last ? $last->display_order + 1 : 1;

        // grab decimal then unset it
        $decimal = null;
        if (isset($input['decimal'])) {
            $decimal = $input['decimal'];
            unset($input['decimal']);
        }

        // create the custom field record
        $field = parent::create($input);

        // Perform schema for new custom field on resource table
        if ( $field && !$this->noSchemaChange($field->type) ) {

            try {

                Schema::table(strtolower($field->resource), function($table) use ($field, $field_map, $decimal) {
                    $parameters = $field_map[$field->type]['parameters'];
                    $column_name = 'custom_field_' . $field->id;

                    if ( in_array($field->type, ['textarea', 'html', 'file']) )  {
                        $table->text($column_name)->nullable()->default(null);
                    } elseif ( in_array($field->type, ['text', 'name', 'email', 'company', 'tel', 'url', 'hidden', 'radio', 'select']) )  {
                        $table->string($column_name, $parameters['length'])->nullable()->default(null);
                    } elseif ( in_array($field->type, ['number', 'money']) )  {
                        if ( empty($decimal) || $decimal == 0 ) {
                            $table->integer($column_name)->nullable()->default(null);
                        } else {
                            $table->decimal($column_name, $parameters['length'], $decimal)->nullable()->default(null);
                        }
                    } elseif ( $field->type == 'checkbox' )  {
                        $table->boolean($column_name)->nullable()->default(null);
                    } elseif ( $field->type == 'date' )  {
                        $table->date($column_name)->nullable()->default(null);
                    } elseif ( $field->type == 'timestamp' )  {
                        $table->dateTime($column_name)->nullable()->default(null);
                    }

                    // create index if the column is searchable
                    if ( $field->is_searchable ) {
                        $table->index([$column_name], $column_name);
                    }

                });

            } catch (\Exception $e) {
                $field = parent::delete($field->id);
                fail('Could not alter table');
            }

        }

        return $field;
    }

    /**
     * update a record
     *
     * @param  array  $input
     * @return object
     */

    public function update($input)
    {
        $previous_field = $this->model;

        if (!empty($input['is_searchable']) && in_array($previous_field->type, ['textarea', 'html'])) {
            fail('Textarea/HTML fields cannot be searchable');
        }

        // Update searchable if needed
        if ( isset($input['is_searchable']) && $input['is_searchable'] != $previous_field->is_searchable ) {
            Schema::table(strtolower($input['resource']), function($table) use ($input, $previous_field) {
                $column_name = 'custom_field_' . $previous_field->id;
                if ( $input['is_searchable'] )  {
                    $table->index([$column_name], $column_name);
                } else {
                    $table->dropIndex($column_name);
                }
            });
        }

        $field = parent::update($input);

        return $field;

    }

    /**
     * delete a record
     *
     * @param  array  $id
     * @return object
     */

    public function delete($id)
    {

        $field = parent::delete($id);

        try {
            Schema::table(strtolower($field->resource), function($table) use ($field) {
                $table->dropColumn('custom_field_' . $field->id);
            });
        } catch (\Exception $e) {
            // fail silently
        }

        return $field;

    }

    /**
     * Set display order for collection of IDs
     *
     * @param array $input
     *
     * @return array
     */
    public function setDisplayOrder($input)
    {
        $fields = [];
        foreach ($input as $data) {
            $id = $data['id'];
            $order = $data['order'];
            $field = CustomField::find($id);
            $field->display_order = $order;
            $field->save();
            $fields[] = $field;
        }
        return $fields;
    }

    /**
     * Prevent creating more than the max allowed fields
     *
     * @param $input
     */
    public function checkMaxLimit($input)
    {
        $count = CustomField::where('resource', $input['resource'])->count();
        if ($count > 100) {
            fail('You have created the maximum number of custom fields for this resource type');
        }
    }

    /**
     * Determine if a field type needs schema change
     *
     * @param $type
     *
     * @return bool
     */
    public function noSchemaChange($type) {
        $no_change = ['header', 'description', 'button', 'submit'];
        return in_array($type, $no_change) ? true : false;
    }

    /**
     * Field map of all column types
     *
     * @return array
     */
    public function getFieldMap() {
        return [
            'text' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 255
                ]
            ],
            'html' => [
                'type' => 'text',
                'parameters' => []
            ],
            'textarea' => [
                'type' => 'text',
                'parameters' => []
            ],
            'name' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 60
                ]
            ],
            'company' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 60
                ]
            ],
            'email' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 155
                ]
            ],
            'tel' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 30
                ]
            ],
            'url' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 200
                ]
            ],
            'hidden' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 255
                ]
            ],
            'file' => [
                'type' => 'text',
                'parameters' => []
            ],
            'number' => [
                'type' => 'decimal',
                'parameters' => [
                    'length' => 18,
                    'decimal' => 0
                ]
            ],
            'money' => [
                'type' => 'decimal',
                'parameters' => [
                    'length' => 18,
                    'decimal' => 2
                ]
            ],
            'date' => [
                'type' => 'date',
                'parameters' => []
            ],
            'checkbox' => [
                'type' => 'boolean',
                'parameters' => []
            ],
            'select' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 60
                ]
            ],
            'radio' => [
                'type' => 'string',
                'parameters' => [
                    'length' => 60
                ]
            ],
            'subscribe' => [
                'type' => 'boolean',
                'parameters' => []
            ],
        ];
    }

}
