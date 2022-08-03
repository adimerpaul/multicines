<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRubroRequest;
use App\Models\Movie;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Rubro;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Movie::with('distributor')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMovieRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMovieRequest $request)
    {
        $movie= Movie::create($request->all());
        return Movie::where('id',$movie->id)->with('distributor')->first();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function show(Movie $movie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function edit(Movie $movie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMovieRequest  $request
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMovieRequest $request, Movie $movie)
    {
        $movie->update($request->all());
        return $movie->with('distributor')->get();
    }
    public function upimagenmovie(UpdateMovieRequest $request)
    {
        //
        $movie= Movie::find($request->id);
        $movie->imagen=$request->imagen;
        $movie->save();

        $movie=Movie::with('distributor')->where('id',$request->id)->first();
        return $movie;
    }
    public function upimagenrubro(UpdateRubroRequest $request)
    {
        //
        $rubro= Rubro::find($request->id);
        $rubro->imagen=$request->imagen;
        $rubro->save();

        return $rubro;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();
    }
}
