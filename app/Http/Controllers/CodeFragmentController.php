<?php

namespace App\Http\Controllers;

use App\Models\CodeFragment;
use Illuminate\Http\Request;

class CodeFragmentController extends Controller
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        try {
            $request->validate(['uuid' => ['required', 'string', 'max:255']]);
            $uuid = $request->input('uuid');
            $data = CodeFragment::where('uuid', $uuid)->orderByDesc('id')->get();
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
