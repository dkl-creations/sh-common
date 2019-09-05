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
        $input['display_order'] = $this->getNextDisplayOrder($input);

        // grab decimal value
        $decimal = isset($input['decimal']) ? $input['decimal'] : null;

        // create the custom field record
        $field = parent::create($input);

        // determine our target table to modify
        $target_table = $this->getTargetTable($field);

        // Perform schema for new custom field on resource table
        if ( $field && !$this->noSchemaChange($field->type) ) {

            try {

                Schema::table($target_table, function($table) use ($field, $field_map, $decimal) {
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

        $target_table = $this->getTargetTable($previous_field);

        // Update searchable if needed
        if ( isset($input['is_searchable']) && $input['is_searchable'] != $previous_field->is_searchable ) {
            Schema::table($target_table, function($table) use ($input, $previous_field) {
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
            $target_table = $this->getTargetTable($field);
            Schema::table($target_table, function($table) use ($field) {
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
        $count = CustomField::when(isset($input['resource']), function($query) use($input) {
            return $query->where('resource', $input['resource']);
        })->when(isset($input['group_id']), function($query) use($input) {
            return $query->where('group_id', $input['group_id']);
        })->count();
        if ($count > 100) {
            fail('You have already created the maximum number of custom fields.');
        }
    }

    /**
     * Determine the next display order value
     *
     * @param $input
     *
     * @return int
     */
    public function getNextDisplayOrder($input)
    {
        $last = CustomField::when(isset($input['resource']), function($query) use($input) {
            return $query->where('resource', $input['resource']);
        })->when(isset($input['group_id']), function($query) use($input) {
            return $query->where('group_id', $input['group_id']);
        })->orderBy('display_order', 'DESC')->first();
        $display_order = $last ? $last->display_order + 1 : 1;
        return $display_order;
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

    /**
     * Determine the target table name
     *
     * @param $field
     *
     * @return string
     */
    protected function getTargetTable($field)
    {
        $table = null;
        if (!empty($field->resource)) {
            $table = strtolower($field->resource);
        } elseif (!empty($field->group_id)) {
            $table = 'listings_' . $field->group_id;
        }
        if ($table == null) {
            fail('Unable to determine target table name');
        }
        return $table;
    }

}
