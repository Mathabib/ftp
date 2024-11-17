<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Flysystem\Filesystem;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;

class FTPController_backup extends Controller
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

            return view('ftp-browse', [
                'contents' => $contents,
                'currentPath' => $path,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to browse: ' . $e->getMessage()]);
        }
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file']);
        $filesystem = $this->getFTPConnection();

        $file = $request->file('file');
        $path = $request->current_path . '/' . $file->getClientOriginalName();

        $filesystem->writeStream($path, fopen($file->getRealPath(), 'r'));
        return back()->with('message', 'File uploaded successfully!');
    }

    public function createFolder(Request $request)
    {
        $filesystem = $this->getFTPConnection();
        $path = $request->current_path . '/' . $request->folder_name;

        $filesystem->createDirectory($path);
        return back()->with('message', 'Folder created successfully!');
    }

    public function delete(Request $request)
    {
        $filesystem = $this->getFTPConnection();
        $filesystem->delete($request->path);

        return back()->with('message', 'Deleted successfully!');
    }

    public function rename(Request $request)
    {
        $filesystem = $this->getFTPConnection();
        $filesystem->move($request->old_path, $request->new_path);

        return back()->with('message', 'Renamed successfully!');
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
}
