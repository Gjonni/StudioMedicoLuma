@props(['columns', 'rows', 'paginate' => true])

<div class="js-datatable"
     data-columns="{{ json_encode($columns) }}"
     data-rows="{{ json_encode($rows) }}"
     data-paginate="{{ $paginate ? 'true' : 'false' }}"></div>
