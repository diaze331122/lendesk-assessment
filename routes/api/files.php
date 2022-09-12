<?php

use Models\User;
use Models\File;

/**
 * Ideally, some of the operations in this file 
 * should be moved to a controller.
 * However, I am leaving it here due to time constraints.
 */

API::group('/files', function () {
    //get user uploaded files
    API::get('/', function ($request, $response, $arguments) {    
        try {
            $user = API::user();

            $files = File::where('user_id', '=', $user->user_id);

            API::success($files);
            
        } catch (\Exception $e) {
            // Silent fail
        }        
    });

    //get user uploaded file
    API::get('/{fileId}', function ($request, $response, $arguments) {    
        try {
            $user = API::user();

            $fileId = $arguments['fileId'];

            $file = File::where('id', '=', $fileId)
                ->first();

            API::success($file);
            
        } catch (\Exception $e) {
            // Silent fail
        }        
    });


    // Update a file 
    API::put('/{fileId}', function ($request, $response, $arguments) {

        $user = API::user();

        $parameters = $request->getQueryParams();

        $fileId = $arguments['fileId'];

        $file = File::where('id', '=', $fileId)
            ->where('user_id','=', $user->user_id)
            ->first();
        
        $s3 = new Aws\S3\S3Client([
            'region'  => env('AWS_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_API_KEY'),
                'secret' => env('AWS_API_SECRET'),
            ]
        ]);

        $result = $s3->putObject([
			'Bucket' => env('AWS_BUCKET'),
			'Key'    => $file->file_name,
			'SourceFile' => $temp_file_location			
		]);

        $file->url = $result->get('ObjectURL');
        $file->save();

        return API::success($file);
    });

    API::post('/upload', function ($request, $response, $arguments) {
        $post = $request->getParsedBody();
        
        





    });

    API::delete('/{fileId}', function ($request, $response, $arguments){
        //
    });

});