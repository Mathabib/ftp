//hanya button dengan atribut data-bs-toggle yang kepilih 
// $(document).on('click','button[data-bs-toggle="modal"]', function() {
//     const path = $(this).data('path');

//     $('#path').val(path);
//     console.log(path)
// })


$(document).on('click','#submit_rename', function() {
    const path = $(this).data('path');

    $('#path').val(path);
    console.log(path)
})