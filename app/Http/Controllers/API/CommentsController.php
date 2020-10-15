<?php

namespace App\Http\Controllers\API;

use App\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        return response()->json([
            'comments' => Comment::all(),
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
            'task_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $comment = Comment::create([
            'body' => $request->body,
            'user_id' => \Auth::user()->id,
            'task_id' => $request->task_id,
        ]);

        return response()->json(['comment' => $comment]);

    }

    #----------------------------------------------------

    /**
     * Display the specified resource.
     *
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(int $comment)
    {
        $validator = \Validator::make(request()->all(), [
            'task_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        return response()->json(['comment' => Comment::where(['id' => $comment, 'task_id' => request()->task_id])->first()]);
    }

    #----------------------------------------------------

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        $validator = \Validator::make($request->all(), [
            'body' => ['required', 'min:4'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $comment = $comment->update([
            'body' => $request->body,
        ]);

        return response()->json(['comment' => $comment ? 'comment updated successfully.' : 'problem updating your comment!']);
    }

    #----------------------------------------------------

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $oldComment = $comment;
        $comment->delete();

        return response()->json([
                'oldComment'    => $oldComment,
                'message'       => "comment deleted successfully."
        ]);
    }
}
