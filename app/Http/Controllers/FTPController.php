<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;

class FTPController extends Controller
{
    protected function getFTPConnection()
    {
        $options = FtpConnectionOptions::fromArray([
            'host' => session('ftp_host'),
            'username' => session('ftp_username'),
            'password' => session('ftp_password'),
            'port' => 21,
            'passive' => true,
            'ssl' => false,
            'timeout' => 30,
        ]);

        return new Filesystem(new FtpAdapter($options));
    }

    public function connect(Request $request)
    {
        $request->validate([
            'ftp_host' => 'required',
            'ftp_username' => 'required',
            'ftp_password' => 'required',
        ]);

        session([
            'ftp_host' => $request->ftp_host,
            'ftp_username' => $request->ftp_username,
            'ftp_password' => $request->ftp_password,
        ]);

        return redirect()->route('ftp.browse');
    }

    public function disconnect()
    {
        session()->forget(['ftp_host', 'ftp_username', 'ftp_password']);
        return redirect()->route('ftp.login');
    }

    public function browse($path = '/')
    {
        $path = trim($path, '/');
        $path = $path ? '/' . $path : '/';

        try {
            $filesystem = $this->getFTPConnection();
            $contents = $filesystem->listContents($path, false);

              // Hitung path sebelumnya
            $previousPath = $path === '/' ? '/' : dirname($path);

            return view('ftp-browse', [
                'contents' => $contents,
                'currentPath' => $path,
                'previousPath' => $previousPath,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to browse: ' . $e->getMessage()]);
        }
    }

    public function upload(Request $request)
    {
       try {
        $request->validate(['file' => 'required|file']);
        $filesystem = $this->getFTPConnection();

        $file = $request->file('file');
        $path = $request->current_path . '/' . $file->getClientOriginalName();

        $filesystem->writeStream($path, fopen($file->getRealPath(), 'r'));
        return back()->with('message', 'File uploaded successfully!');
       } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Upload File Failed: ' . 'You do not have permission for Upload File']);
       }
    }

    public function createFolder(Request $request)
    {
        try {
            $filesystem = $this->getFTPConnection();
            $path = $request->current_path . '/' . $request->folder_name;

            $filesystem->createDirectory($path);
            return back()->with('message', 'Folder created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Create Folder Failed: ' . 'You do not have permission for Create Folder']);
        }
    }

    public function delete(Request $request)
    {
        if( strtolower($request->type) == 'file'){
            // @dd('ini yang file');
            try{
                $filesystem = $this->getFTPConnection();
                $filesystem->delete($request->path);
        
                return back()->with('message', 'Deleted successfully!');
            } catch(\Exception $e) {
                // return back()->withErrors(['error' => 'Delete Failied: ' . $e->getMessage()]);
                return back()->withErrors(['error' => 'Delete Failed: ' . 'You do not have permission for deleting file']);
            }
           
        } else {
            // @dd('ini yang direktori');
            try{
                $filesystem = $this->getFTPConnection();
                $filesystem->deleteDirectory($request->path);
        
                return back()->with('message', 'Deleted successfully!');
            } catch(\Exception $e){
                return back()->withErrors(['error' => 'Delete Failed: ' . 'You do not have permission for deleting folder']);
            }
        }
       
    }

    public function rename(Request $request)
    {
    //    @dd(dirname($request->old_path));
        $request->validate([
            'old_path' => 'required|string',
            'new_path' => 'required|string'
        ]);
        $filesystem = $this->getFTPConnection();

        try {

            $newPath = dirname($request->old_path) . '/' . $request->new_path;
            $filesystem->move($request->old_path, $newPath);
            return back()->with('message', 'Renamed successfully!');

        } catch(\Exception $e) {
            return back()->withErrors(['error' => 'Rename failed: ' . $e->getMessage()]);
        }
       
       
    }

    public function download($path)
    {
        $filesystem = $this->getFTPConnection();
        $stream = $filesystem->readStream($path);
        $contents = stream_get_contents($stream);

        return response($contents)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . basename($path) . '"');
    }

    public function test(Request $request)
    {
        @dd($request);
    }
}
