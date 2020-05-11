<?php

namespace App\Http\Controllers;

use App\Models\CodeFragment;
use Illuminate\Http\Request;

class CodeFragmentController extends Controller
{
    public function view()
    {
        $data = ['title' => '代码片段'];
        return view('codeFragment/index')->with($data);
    }

    /**
     * index data
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = CodeFragment::query();
        $query->when($request->has('uuid'), function ($query, $keyword) {
            return $query->where('uuid', $keyword);
        });
        if ($request->has('uuid')) {
            $data = $query->first();
        }
        return $data;
    }
}
