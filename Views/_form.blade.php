@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
          <div class="panel-heading">Create User</div>

          <div class="panel-body">
            <form class="form-horizontal" method="POST" action="{{$action}}">
              @csrf
              @method($method)

              <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                <label for="name" class="col-md-4 control-label">Name</label>

                <div class="col-md-6">
                  <input id="name"
                         type="text"
                         class="form-control"
                         name="name"
                         value="{{$user->name}}"
                         required
                         autofocus>

                  @if ($errors->has('name'))
                    <span class="help-block">
                      <strong>{{ $errors->first('name') }}</strong>
                    </span>
                  @endif
                </div>
              </div>

              <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                <label for="username" class="col-md-4 control-label">Username</label>

                <div class="col-md-6">
                  <input id="username"
                         type="text"
                         class="form-control"
                         name="username"
                         value="{{$user->username}}"
                         required
                         autofocus>

                  @if ($errors->has('username'))
                    <span class="help-block">
                      <strong>{{ $errors->first('username') }}</strong>
                    </span>
                  @endif
                </div>
              </div>

              <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                <div class="col-md-6">
                  <input
                    id="email"
                    type="email"
                    class="form-control"
                    name="email"
                    value="{{$user->email}}"
                    required>
                  @if ($errors->has('email'))
                    <span class="help-block">
                      <strong>{{ $errors->first('email') }}</strong>
                    </span>
                  @endif
                </div>
              </div>

              <div class="form-group{{ $errors->has('roles') ? ' has-error' : '' }}">
                <label for="email" class="col-md-4 control-label">Account Role</label>

                <div class="col-md-6">
                  <select
                    id="role"
                    class="form-control"
                    name="roles">
                    <option value="">Assign Role</option>
                    @foreach($roles as $key => $role)
                      <option value="{{$key}}" {{ ($user->role == $key ) ? 'selected' : 'none'}}>{{$role}}</option>
                    @endforeach
                  </select>

                  @if ($errors->has('email'))
                    <span class="help-block">
                      <strong>{{ $errors->first('email') }}</strong>
                    </span>
                  @endif
                </div>
              </div>

              <div class="form-group mb:4@lg">
                <div class="col-md-6 col-lg-offset-4" id="role_container">
                  @php($roles = explode(',', $user->role))
                  @if(!empty($user->role))
                    @foreach($roles as $role)
                      <button class="btn btn-{{$roleColors[$role]}} btn-xs role-badge"
                              data-role="{{$role}}"
                              type="button">{{ $role }} <i class="fa fa-close"></i></button>
                    @endforeach
                  @else
                    <button class="btn btn-secondary btn-xs" type="button">none</button>
                  @endif
                  <input type="hidden" name="roles" id="roles" value="{{$user->role}}">
                  <input type="hidden" id="role_colors" value="{{json_encode($roleColors)}}">
                </div>
              </div>

              <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                <label for="password" class="col-md-4 control-label">Password</label>

                <div class="col-md-6">
                  <input
                    id="password"
                    type="password"
                    class="form-control"
                    name="password"
                    value="{{$user->password}}"
                    required>

                  @if ($errors->has('password'))
                    <span class="help-block">
                      <strong>{{ $errors->first('password') }}</strong>
                    </span>
                  @endif
                </div>
              </div>

              <div class="form-group">
                <label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>

                <div class="col-md-6">
                  <input id="password-confirm"
                         type="password"
                         class="form-control"
                         name="password_confirmation"
                         value="{{$user->password}}"
                         required>
                </div>
              </div>

              <div class="form-group">
                <div class="col-md-6 col-md-offset-4">
                  <button type="submit" class="btn btn-success">
                    Save
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      $.getScript(`{{ URL::asset('js/user-form.min.js') }}`);
    });
  </script>
@endsection
