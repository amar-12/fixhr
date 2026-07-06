<?php

namespace App\Http\Controllers;

use ChandraHemant\HtkcUtils\DynamicSearchHelper;
use Illuminate\Http\Request;

class DynamicSearchController extends Controller
{
    /**
     * Laeravel Custom Dynamic Search Method
     *
     * @package		Laravel
     * @subpackage  Custom Search
     * @category	Method
     * @author		Hemant Chandra
     */
    /*
        * Custom Dynamic Search Example
        {
            "model":"CustomerDetail",
            "resource":"CustomerResource",
            "column":["name","phone","email"],
            "where":["cd_b_id"=>1],
            "value":"Deepak"
        }
    */
    public function search(Request $request)
    {
        // Ensure 'where' is provided and is an array
        $searchColumns = $request->input('where', []); // Default to an empty array if 'where' is not provided

        $helper = new DynamicSearchHelper(
            searchColumns: $searchColumns, // Provide your search columns if needed
            withPagination: true,
            queryMode: false,
        );



        // Get the dynamic search data
        $result = $helper->getDynamicSearchData();

        // Ensure this is returned properly as a response
        return $result;
    }
}
