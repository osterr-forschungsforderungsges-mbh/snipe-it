@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.backups') }}
@parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-default pull-right" style="margin-left: 5px;"> 
      {{ trans('general.back') }}
    </a>

    <form method="POST" style="display: inline">
      {{ Form::hidden('_token', csrf_token()) }}
            <button class="btn btn-primary {{ (config('app.lock_passwords')) ? ' disabled': '' }}">{{ trans('admin/settings/general.generate_backup') }}</button>
      </form>

@stop

{{-- Page content --}}
@section('content')


<div class="row">

  <div class="col-md-8">
    
    <div class="box box-default">
      <div class="box-body">
       
        
          
        <div class="table-responsive">
          
            <table
                    data-cookie="true"
                    data-cookie-id-table="system-backups"
                    data-pagination="true"
                    data-id-table="system-backups"
                    data-search="true"
                    data-side-pagination="client"
                    data-sort-order="asc"
                    id="system-backups"
                    class="table table-striped snipe-table">
            <thead>
              <tr>
              <th data-sortable="true">File</th>
              <th data-sortable="true" data-field="modified_display" data-sort-name="modified_value">Created</th>
              <th data-field="modified_value" data-visible="false"></th>
              <th data-sortable="true">Size</th>
              <th><span class="sr-only">{{ trans('general.delete') }}</span></th>
              </tr>
            </thead>
            <tbody>
            @foreach ($files as $file)
            <tr>
              <td>
                  <a href="{{ route('settings.backups.download', [$file['filename']]) }}">
                      {{ $file['filename'] }}
                  </a>
              </td>
              <td>{{ $file['modified_display'] }} </td>
              <td>{{ $file['modified_value'] }} </td>
              <td>{{ $file['filesize'] }}</td>
              <td>

                  @can('superadmin')
                      <a data-html="false"
                         class="btn delete-asset btn-danger btn-sm {{ (config('app.lock_passwords')) ? ' disabled': '' }}" data-toggle="modal" href="{{ route('settings.backups.destroy', $file['filename']) }}" data-content="{{ trans('admin/settings/message.backup.delete_confirm') }}" data-title="{{ trans('general.delete') }}  {{ e($file['filename']) }} ?" onClick="return false;">
                          <i class="fas fa-trash icon-white" aria-hidden="true"></i>
                          <span class="sr-only">{{ trans('general.delete') }}</span>
                      </a>

                     <a data-html="true" href="{{ route('settings.backups.restore', $file['filename']) }}" class="btn btn-warning btn-sm restore-asset {{ (config('app.lock_passwords')) ? ' disabled': '' }}" data-toggle="modal" data-content="Yes, restore it. I acknowledge that this will overwrite any existing data currently in the database. This will also log out all of your existing users (including you)." data-title="Are you sure you wish to restore your database from {{ e($file['filename']) }}?" onClick="return false;">
                      <i class="fas fa-retweet" aria-hidden="true"></i>
                      <span class="sr-only">Restore</span>
                    </a>
                     
                  @endcan
              </td>
            </tr>
            @endforeach
            </tbody>
          </table>
      </div> <!-- end table-responsive div -->
    </div> <!-- end box-body div -->
</div> <!-- end box div -->
</div> <!-- end col-md div -->

   <!-- side address column -->
  <div class="col-md-4">

    <div class="box box-default">
      <div class="box-header with-border">
        <h2 class="box-title">
          <i class="far fa-file-archive" aria-hidden="true"></i>
          Upload Backup</h2>
        <div class="box-tools pull-right">
        </div>
      </div><!-- /.box-header -->

      <div class="box-body">

        <p>Backup files on the server are stored in: <code>{{ $path }}</code></p>
        
      {{ Form::open([
        'method' => 'POST',
        'route' => 'settings.backups.upload',
        'files' => true,
        'class' => 'form-horizontal' ]) }}
        @csrf

        
      <div class="form-group {{ $errors->has((isset($fieldname) ? $fieldname : 'image')) ? 'has-error' : '' }}" style="margin-bottom: 0px;">
        <div class="col-md-8 col-xs-8">
            <!-- screen reader only -->
            <input type="file" id="file" name="file" aria-label="file" class="sr-only">

             <!-- displayed on screen -->
            <label class="btn btn-default col-md-12 col-xs-12" aria-hidden="true">
              <i class="fas fa-paperclip" aria-hidden="true"></i>
                {{ trans('button.select_file')  }}
                <input type="file" name="file" class="js-uploadFile" id="uploadFile" data-maxsize="{{ Helper::file_upload_max_size() }}" accept="application/zip" style="display:none;" aria-label="file" aria-hidden="true">
            </label>   

        </div>
        <div class="col-md-4 col-xs-4">
            <button class="btn btn-primary col-md-12 col-xs-12" id="uploadButton" disabled>Upload</button>
        </div>
        <div class="col-md-12">
          
          <p class="label label-default col-md-12" style="font-size: 120%!important; margin-top: 10px; margin-bottom: 10px;" id="uploadFile-info"></p>
          
          <p class="help-block" style="margin-top: 10px;" id="uploadFile-status">{{ trans_choice('general.filetypes_accepted_help', 1, ['size' => Helper::file_upload_max_size_readable(), 'types' => '.zip']) }}</p>     
          {!! $errors->first('image', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}

        </div>        
    </div>
    
    {{ Form::close() }}
      </div>
    </div>

    <div class="box box-warning">
      <div class="box-header with-border">
        <h2 class="box-title">
          <i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>  Restoring from Backup</h2>
        <div class="box-tools pull-right">
        </div>
      </div><!-- /.box-header -->
      <div class="box-body">
        
      <p>
        Use the restore (<i class="text-white fas fa-retweet" aria-hidden="true"></i>) button to 
        restore from a previous backup. (This does not currently with with S3 file storage.)<br><br>Your <strong>entire {{ config('app.name') }} database and any uploaded files will be completely replaced</strong> by what's in the backup file.  
      </p>
        
      <p class="text-danger" style="font-weight: bold">
        You will be logged out after your restore is complete.
      </p>

      <p>
        Very large backups may time out on the restore attempt and may still need to be run via command line.  
      </p>
      
    </div>
  </div>
    
        </div> <!-- end col-md-12 form div -->
   </div> <!-- end form group div -->

  </div> <!-- end col-md-3 div -->
</div> <!-- end row div -->

@stop

@section('moar_scripts')

    @include ('partials.bootstrap-table')

    
    <script>
      /*
      * This just disables the upload button via JS unless they have actually selected a file.
      *
      * Todo: - key off the javascript response for JS file upload info as well, so that if it fails that 
      * check (file size and type) we should also leave it disabled.
      */

      $(document).ready(function() {
        
        $("#uploadFile").on('change',function(event){

            if ($('#uploadFile').val().length == 0) {
              $("#uploadButton").attr("disabled", true);
            } else {
              $('#uploadButton').removeAttr('disabled');
            }
            
        });
      });
  </script>
@stop

