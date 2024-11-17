<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FTP Browser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/93bcf242b7.js" crossorigin="anonymous"></script>
</head>
<body>
   
<div class="container mt-5">
    <h1>FTP Browser</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="POST" action="{{ route('ftp.disconnect') }}">
            @csrf
            <button class="btn btn-danger">Logout</button>
        </form>
        <form method="POST" action="{{ route('ftp.createFolder') }}" class="d-flex">
            @csrf
            <input type="hidden" name="current_path" value="{{ $currentPath }}">
            <input type="text" name="folder_name" class="form-control me-2" placeholder="New Folder Name" required>
            <button type="submit" class="btn btn-success">Create Folder</button>
        </form>
    </div>

    <form method="POST" action="{{ route('ftp.upload') }}" enctype="multipart/form-data" class="mb-3">
        @csrf
        <input type="hidden" name="current_path" value="{{ $currentPath }}">
        <div class="d-flex align-items-center">
            <input type="file" name="file" class="form-control me-2" required>
            <button type="submit" class="btn btn-primary">Upload</button>
        </div>
    </form>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Size</th>
            <th>Permissions</th>
            <th>Last Modified</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
            <td>
                <i class="fas fa-folder text-warning"></i>
                <a href="{{ route('ftp.browse', ['path' => $previousPath]) }}">back ..</a>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            @foreach ($contents as $item)
                <tr>
                    <td>
                        @if($item->isDir())
                            <i class="fas fa-folder text-warning"></i>
                        @elseif(in_array(pathinfo($item->path(), PATHINFO_EXTENSION), ['pdf']))
                            <i class="fas fa-file-pdf text-danger"></i>
                        @elseif(in_array(pathinfo($item->path(), PATHINFO_EXTENSION), ['xls', 'xlsx']))
                            <i class="fas fa-file-excel text-success"></i>
                        @elseif(in_array(pathinfo($item->path(), PATHINFO_EXTENSION), ['doc', 'docx']))
                            <i class="fas fa-file-word text-primary"></i>
                        @elseif(in_array(pathinfo($item->path(), PATHINFO_EXTENSION), ['ppt', 'pptx']))
                            <i class="fas fa-file-powerpoint text-warning"></i>
                        @else
                            <i class="fas fa-file text-secondary"></i>
                        @endif
                        <a href="{{ $item->isDir() ? route('ftp.browse', trim($item->path(), '/')) : '#' }}" 
                           class="text-decoration-none ms-2">
                            {{ basename($item->path()) }}
                            {{-- {{ $item->path() }} --}}
                        </a>
                    </td>
                    <td>{{ $item->isDir() ? 'Directory' : 'File' }}</td>
                    <td>{{ $item->isDir() ? '-' :  number_format($item->fileSize() / 1048576, 2) . ' MB' }}</td>
                    <td>{{ $item->visibility() ?? '-' }}</td>
                    <td>{{ $item->lastModified() ? \Carbon\Carbon::createFromTimestamp($item->lastModified())->toDateTimeString() : '-' }}</td>
                    <td>
                        @if(!$item->isDir())
                            <a href="{{ route('ftp.download', trim($item->path(), '/')) }}" class="btn btn-sm btn-primary">Download</a>
                        @endif
                        <form method="POST" action="{{ route('ftp.delete') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="type" value="{{  $item->isDir() ? 'Directory' : 'File' }}">
                            <input type="hidden" name="path" value="{{ $item->path() }}">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-primary" id="submit_rename" data-bs-toggle="modal" data-bs-target="#exampleModal" data-path="{{ $item->path() }}">
                            rename
                          </button>
                          
                    </td>
                </tr>
            @endforeach
        </tbody>
        
    </table>
</div>

 <!-- Button trigger modal -->

  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('ftp.rename') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="old_path" id="path" value="">
                <label for="new_path" class="form-label">New Name</label>
                <input type="text" class="form-control" name="new_path">
            
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
      </div>
    </div>
  </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('js/ftp-browser.js') }}"></script>
</body>
</html>
