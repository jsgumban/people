<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\People;
use App\Http\Resources\People as PeopleResource;

class PeopleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get people
        $people = People::paginate(10);

        // Return collection of people as a resource
        return PeopleResource::collection($people);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $people = new People;

        $people->id = $request->input('people_id');
        $people->first_name = $request->input('first_name');
        $people->last_name = $request->input('last_name');
        $people->birthday = $request->input('birthday');


        if( $people->save() ) {
            return new PeopleResource($people);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Get certain people
        $people = People::findOrFail($id);

        // Return people as a resource
        return new PeopleResource($people);
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
        $people = People::findOrFail($id);

        $people->first_name = $request->input('first_name');
        $people->last_name = $request->input('last_name');
        $people->birthday = $request->input('birthday');

        if( $people->save() ) {
            return new PeopleResource($people);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Get certain people
        $people = People::findOrFail($id);


        if( $people->delete() ) {
            return new PeopleResource($people);
        }
    }
}
