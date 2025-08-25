@props(["action" => "", "method" => "GET", "class" => ""])

<form @if($action) action="{{ $action }}" @endif method="{{ $method }}" class="{{ $class }} space-y-8">
    @csrf
    {{ $slot }}
</form>
