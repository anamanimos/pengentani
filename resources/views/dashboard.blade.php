@extends('layouts.metronic')

@section('title', 'Beranda')
@section('page_title', 'Selamat Datang, ' . Auth::user()->name . '!')

@section('content')
    <!-- Konten Dashboard Anda akan ditambahkan di sini -->
@endsection
