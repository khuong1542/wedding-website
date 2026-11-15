@if ($errors->any())
    <script type="application/json" data-validation-errors>@json($errors->getMessages())</script>
@endif
