@extends('errors::layout', [
    'disableHome' => true,
    'title' => __('errors.error_pages.503.title'),
])

@section('message', __('errors.error_pages.503.message'))
@section('explanation', __('errors.error_pages.503.explanation'))
