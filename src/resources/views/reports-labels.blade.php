@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9">

        <div class="container" id="listing_teams">
            <div class="row mt-3" v-show="teams.length > 0">
                <team-card class="s12 m4" v-for="(team, index) in teams" :key="team.id" :team="team" :index="index"
                             v-on:edit-team="editTeam" v-on:delete-team="deleteTeam"></team-card>
            </div>
            <div class="col s12" v-if="teams.length === 0">
                @component('layouts.blocks.tabler.empty-fullpage')
                    @slot('title')
                        No Teams
                    @endslot
                    You can add one or more teams to organise your projects and workflow.
                    @slot('buttons')
                        <a href="#" v-on:click.prevent="createTeam" class="btn btn-primary btn-sm">Add Team</a>
                    @endslot
                @endcomponent
            </div>
            @include('modules-people::modals.team')
        </div>

    </div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
        var vm = new Vue({
            el: '#listing_teams',
            data: {
                teams: {!! json_encode(!empty($teams) ? $teams : []) !!},
                team: {name: '', description: ''},
            },
            computed: {
                showTeamId: function () {
                    return typeof this.team.id !== 'undefined';
                }
            },
            methods: {
                createTeam: function () {
                    this.team = {name: '', description: ''};
                    $('#manage-team-modal').modal('show');
                },
                editTeam: function (index) {
                    let team = typeof this.teams[index] !== 'undefined' ? this.teams[index] : null;
                    if (team === null) {
                        return;
                    }
                    this.team = team;
                    $('#manage-team-modal').modal('show');
                },
                deleteTeam: function (index) {
                    let teams = typeof this.teams !== 'undefined' ? this.teams : null;
                    let team = typeof this.teams[index] !== 'undefined' ? this.teams[index] : null;
                    if (team === null) {
                        return;
                    }
                    ///team.is_default = team.is_default ? 1 : 0;
                    this.team = team;
                    let e_count = typeof team.counts.employees !== 'undefined' ? team.counts.employees : 0;
                    let context = this;
                    if (e_count<1) {
                        Swal.fire({
                            title: "Are you sure?",
                            text: "You are about to delete team " + context.team.name,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes, delete it!",
                            showLoaderOnConfirm: true,
                            preConfirm: (teams_delete) => {
                            return axios.delete("/mpe/people-teams/" + context.team.id)
                                .then(function (response) {
                                    //console.log(response);
                                    context.teams.splice(index, 1);
                                    return swal("Deleted!", "The team was successfully deleted.", "success");
                                })
                                .catch(function (error) {
                                    var message = '';
                                    if (error.response) {
                                        // The request was made and the server responded with a status code
                                        // that falls out of the range of 2xx
                                        var e = error.response.data.errors[0];
                                        message = e.title;
                                    } else if (error.request) {
                                        // The request was made but no response was received
                                        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                                        // http.ClientRequest in node.js
                                        message = 'The request was made but no response was received';
                                    } else {
                                        // Something happened in setting up the request that triggered an Error
                                        message = error.message;
                                    }
                                    return swal("Delete Failed", message, "warning");
                                });
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        })
                    /*.then(function() {
                        //Swal.fire('Ajax request finished!')
                    })*/
                    } else {
                        Swal.fire({
                            title: "Unable to Delete!",
                            text: "The team \"" + team.name + "\" has " + e_count + " employee(s). Remove them first and the retry deleting.",
                            type: "error"
                        })
                    }
                }
            }
        });

        new Vue({
            el: '#sub-menu-action',
            data: {

            },
            methods: {
                createTeam: function () {
                    vm.createTeam();
                }
            }
        })
    </script>
@endsection