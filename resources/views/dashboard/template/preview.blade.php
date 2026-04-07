@extends('layouts.admin')

@section('content')

<div class="card card-shadow">
    <div class="card-header">
        <h5 class="text-success fw-semibold mb-0">Preview Template</h5>
    </div>

    <div class="card-body p-0">
        <iframe 
            src="https://docs.google.com/gview?url={{ $url }}&embedded=true"
            style="width:100%; height:80vh;" 
            frameborder="0">
        </iframe>
    </div>
</div>

@endsection