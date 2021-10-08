<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BlacklistWord;

class BlacklistWordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.seller');

        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $blacklist_ws = BlacklistWord::orderBy('updated_at','desc')->paginate(40);

        if(!$blacklist_ws) return;

        return view('blacklist.index')
            ->with('blacklist_ws', $blacklist_ws);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('blacklist.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!$request->has('keyword'))
            return back()->with('message', "not found");

        $validated = $request->validate([
            'keyword' => 'required|unique:blacklist_words|max:100',
        ]);

        if(!$validated) return back()->with('message', "invalid");

        $blacklist_w = new BlacklistWord();
        $blacklist_w -> keyword = $request->keyword;
        $blacklist_w -> type = $request->type;
        $blacklist_w ->save();

        return back()->with('message', "success");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $blacklist_w = BlacklistWord::find($id);
        if(!$blacklist_w) return;

        return view('blacklist.edit')
            ->with('blacklist_w', $blacklist_w);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $blacklist_w = BlacklistWord::find($id);

        if(!$blacklist_w) return;

        $validated = $request->validate([
            'keyword' => 'required|unique:blacklist_words,id|max:100',
        ]);

        if(!$validated) return back()->with('message', "invalid");

        $blacklist_w -> keyword = $request->keyword;
        $blacklist_w -> type = $request->type;
        $blacklist_w ->save();

        return redirect('blacklist/index')->with('message', 'updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blacklist_w = BlacklistWord::find($id);

        if (!$blacklist_w) return back()->withInput();

        $blacklist_w->delete();

        return redirect('blacklist/index')->with('message', 'deleted');
    }

    /**
     * Get all blackwork to client
     */
    public function getBlackWordList()
    {
        $blacklist_ws = BlacklistWord::all('keyword');

        if(!$blacklist_ws) return;

        return $blacklist_ws;
    }
}
