<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Reply;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'replies' => Reply::all()
        ]);
    }

    #----------------------------------------------------

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'body' => ['required', 'min:4'],
            'comment_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $reply = Reply::create([
            'body' => $request->body,
            'comment_id' => $request->comment_id,
        ]);

        return response()->json(['reply' => $reply]);
    }

    #----------------------------------------------------

    /**
     * Display the specified resource.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function show(Reply $reply)
    {
        return response()->json(['reply' => $reply]);
    }

    #----------------------------------------------------

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reply $reply)
    {
        $validator = \Validator::make($request->all(), [
            'body' => ['required', 'min:4'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $reply = $reply->update([
            'body' => $request->body,
        ]);

        return response()->json(['reply' => $reply ? 'reply updated successfully.' : 'problem updating your reply!']);
    }

    #----------------------------------------------------

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reply  $reply
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reply $reply)
    {
        $oldReply = $reply;
        $reply->delete();

        return response()->json([
                'oldReply'    => $oldReply,
                'message'       => "reply deleted successfully."
        ]);
    }
}
