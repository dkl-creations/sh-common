<?php

namespace DklCreations\SHCommon\Controllers;

use DklCreations\SHCommon\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DklCreations\SHCommon\Controllers\BaseController;
use DklCreations\SHCommon\Helpers\Cloud;

class CustomFieldController extends BaseController
{

    /**
     * Declare services to be injected
     */
    protected $services = [
        'customFieldService' => \DklCreations\SHCommon\Services\CustomFieldService::class
    ];

    /**
     * Find field based on unique ID
     *
     * @param Request $request
     * @param int    $id
     *
     * @return \Laravel\Lumen\Application|mixed
     */
    public function find(Request $request, $id)
    {
        $field = CustomField::findOrFail($id);
        return \Output::data($field)->json();
    }

    /**
     * List all fields (for current org)
     *
     * @param Request $request
     * @param int    $id
     *
     * @return \Laravel\Lumen\Application|mixed
     */
    public function list(Request $request)
    {
        $fields = CustomField::when($request->has('resource'), function ($query) use ($request) {
            return $query->where('resource', $request->input('resource'));
        })->when($request->has('group_id'), function ($query) use ($request) {
            return $query->where('group_id', $request->input('group_id'));
        })->when($request->has('set_id'), function ($query) use ($request) {
            return $query->where('set_id', $request->input('set_id'));
        })->get();
        return \Output::data($fields)->json();
    }

    /**
     * Create a new field
     *
     * @param Request $request
     *
     * @return \Laravel\Lumen\Application|mixed
     */
    public function create(Request $request)
    {
        $input = $request->all();
        $field = $this->customFieldService->create($input);
        return \Output::message('Custom Field created successfully')->data($field)->json();
    }

    /**
     * Update a field
     *
     * @param Request $request
     * @param int    $id
     *
     * @return \Laravel\Lumen\Application|mixed
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $field = $this->customFieldService->load($id)->update($input);
        return \Output::message('Custom Field has been updated successfully')->data($field)->json();
    }

    /**
     * Delete an existing field
     *
     * @param Request $request
     * @param int    $id
     *
     * @return \Laravel\Lumen\Application|mixed
     */
    public function delete(Request $request, $id)
    {
        $field = $this->customFieldService->delete($id);
        return \Output::message('Custom Field has been deleted')->data($field)->json();
    }

    /**
     * Set display orders
     *
     * @param Request $request
     *
     * @return \Laravel\Lumen\Application|mixed
     */
    public function setDisplayOrder(Request $request)
    {
        $fields = $this->customFieldService->setDisplayOrder($request->all());
        return \Output::message('Custom Field order has been updated')->data($fields)->json();
    }

    /**
     * List all custom fields for a given resource
     *
     * @param Request $request
     *
     * @return \DklCreations\SHCommon\Helpers\Output
     */
    public function listByResource(Request $request)
    {
        $fields = CustomField::where('resource', $request->input('resource'))->where('is_active', true)->get();
        return \Output::data($fields)->json();
    }

    /**
     * List all custom fields for a given group ID
     *
     * @param Request $request
     *
     * @return \DklCreations\SHCommon\Helpers\Output
     */
    public function listByGroup(Request $request)
    {
        $fields = CustomField::where('group_id', $request->input('group_id'))->where('is_active', true)->get();
        return \Output::data($fields)->json();
    }

    /**
     * List all custom fields for a given set ID
     *
     * @param Request $request
     *
     * @return \DklCreations\SHCommon\Helpers\Output
     */
    public function listBySet(Request $request)
    {
        $fields = CustomField::where('set_id', $request->input('set_id'))->where('is_active', true)->get();
        return \Output::data($fields)->json();
    }

    /**
     * Manage a file uploaded via custom field
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function manageFile(Request $request)
    {
        $data = json_decode($request->header('X-Data'), true);
        $path = $data['path'];
        $resource = $data['resource'];
        $resource_id = $data['resource_id'];
        $field_id = $data['field_id'];

        if ($request->method() == 'GET') {
            $id = $request->input('load');
            $column_value = $this->getColumnValue($resource, $resource_id, $field_id);

            $filename = $id;
            foreach ( $column_value as $index => $upload ) {
                if ( $upload['file'] == $id ) {
                    $filename = $upload['name'];
                }
            }

            $file_data = Cloud::getFile($path . '/' . $id);

            return response($file_data['contents'])
                ->header('Access-Control-Expose-Headers', 'Content-Disposition, Content-Length, X-Content-Transfer-Id')
                ->header('Content-Type', $file_data['type'])
                ->header('Content-Length', $file_data['size'])
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');

        } else {

            $id = DB::transaction(function() use($request, $resource, $resource_id, $field_id, $path) {

                $column_value = $this->getColumnValue($resource, $resource_id, $field_id, true);

                switch ( $request->method() ) {
                    case 'POST':
                        $file = $request->file('custom_field_upload');
                        $original_name = $file->getClientOriginalName();
                        $id = Cloud::uploadFile($path, $file);

                        $data = [
                            'name' => $original_name,
                            'file' => $id,
                        ];
                        $column_value[] = $data;
                        DB::table(strtolower($resource))->where('id', $resource_id)->update([
                            'custom_field_' . $field_id => json_encode(array_values($column_value))
                        ]);

                        return $id;

                        break;
                    case 'DELETE':
                        $id = $request->has('load') ? $request->input('load') : $request->getContent();

                        Cloud::deleteFile($path . '/' . $id);
                        foreach ( $column_value as $index => $upload ) {
                            if ( $upload['file'] == $id ) {
                                unset($column_value[$index]);
                            }
                        }
                        DB::table(strtolower($resource))->where('id', $resource_id)->update([
                            'custom_field_' . $field_id => json_encode(array_values($column_value))
                        ]);

                        return $id;

                        break;
                }

            });

            return response($id);

        }

    }

    /**
     * Get the column value for a custom field on the resource table
     *
     * @param      $resource
     * @param      $resource_id
     * @param      $field_id
     * @param bool $lock
     *
     * @return array|mixed
     */
    protected function getColumnValue($resource, $resource_id, $field_id, $lock = false)
    {
        if ($lock) {
            $row = DB::table(strtolower($resource))->lockForUpdate()->find($resource_id);
        } else {
            $row = DB::table(strtolower($resource))->find($resource_id);
        }
        if (empty($row->id)) {
            fail('Unable to locate record');
        }
        $column_value = $row->{'custom_field_' . $field_id};
        $column_value = !empty($column_value) ? json_decode($column_value, true) : [];
        return $column_value;
    }

}
