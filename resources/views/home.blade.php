@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}


                    <button class="getauthurl btn btn-info">Add Google</button>



                    <form action="{{route('create_sheet')}}" method="post">
                        @csrf
                        <label for="">Sheet Name</label>
                        <input type="text" class="form-control" name="title" required>

                        <button class="btn btn-primary mt-2">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $('.getauthurl').click(function (param) {
        $.ajax({
            type: "get",
            url: "{{route('getAuthUrl')}}",
            dataType: "json",
            success: function (res) {
                console.log(res);
                window.open(res, "Google Auth", "location=yes");
            }
        });
    })
</script>
@endsection
