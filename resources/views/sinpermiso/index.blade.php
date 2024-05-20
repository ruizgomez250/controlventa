@extends('adminlte::page')

@section('content')



    <h5 class=" custom-heading" id="exampleModalLabel">No tiene permiso para acceder a este recurso</h5>
    <div class="container">
        
        <!-- Perrito durmiendo -->
        <img class="dog" src="{{ asset('vendor/adminlte/dist/img/snoopy.gif') }}" alt="Snoopy">
        
        <!-- Pajarito tratando de despertar -->
        <img class="bird" src="{{ asset('vendor/adminlte/dist/img/bird.png') }}" alt="Bird">
    </div>

@endsection
