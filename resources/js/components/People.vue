<template>
    <div>
        <div class="container bg-light mt-4 mb-3 pb-4 pt-4 pl-5 pr-5">
            <!-- action buttons -->
            <div class="mb-5">
                <button class="btn btn-info float-right mb-3  ml-2" data-toggle="modal" data-target="#personModal" @click="showAddPersonModal()">
                    Add Person
                </button>
                <button class="btn btn-info float-right mb-3"  @click="filterAge = !filterAge">
                    Filter Age
                </button>
            </div>

            <!-- alert component -->
            <div class="alert alert-success alert-dismissible" role="alert" v-if="successOperation">
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">×</span>
                </button>
                SUCCESS : Operation has been successfully completed.
            </div>


            <!-- search bar -->
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Search" v-model="search" v-on:input="debounceSearchInput" >
            </div>

            <!-- filter by age container -->
            <div class="" v-if="filterAge">
                <div class="font-weight-bold">FILTER AGE</div>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <select v-model="filterAgeType">
                                <option value="equal">Age is</option>
                                <option value="notequal">Age is not</option>
                                <option value="greater">Age is greater or equal to</option>
                                <option value="lesser">Age is lesser or equal to</option>
                            </select>
                        </div>
                    </div>
                    <input type="number" class="form-control" v-model="filterAgeValue">
                </div>
            </div>

            <div class="card card-body mb-4 mt-4" v-if="people.length == 0">
                <h3>No Records.</h3>
            </div>

            <!-- list of people -->
            <div class="card card-body mb-4 mt-4" v-for="person in people" v-bind:key="person.id">
                <h4> {{ person.first_name + ' ' + person.last_name }} </h4>
                <h6>Birthday: {{ person.birthday }}</h6>
                <h6>Age: {{ calculateAge(person.birthday) }}</h6>
                <hr>
                <div class="row">
                    <div class="col-lg-4 offset-lg-8 col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-block btn-success" data-toggle="modal" data-target="#personModal" @click="fetchPerson(person.id, 'edit')" >Edit</button>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-block btn-danger" data-toggle="modal" data-target="#personModal" @click="fetchPerson(person.id, 'remove')">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- person info modal -->
            <div class="modal fade" id="personModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{ edit? 'Update Information' : remove? 'Are you absolutely sure?': add? 'Add Information': ''}} </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert-dismissible small" v-if="errors.length">
                                <button type="button" class="close" data-dismiss="alert">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <b>Please correct the following error(s):</b>
                                <ul>
                                    <li v-for="error in errors">{{ error }}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <label>First name</label>
                                <input type="text" class="form-control mb-2" placeholder="First name" v-model="person.first_name" :disabled="remove">
                            </div>
                            <div class="form-group">
                                <label>Last name</label>
                                <input type="text" class="form-control mb-2" placeholder="Last name" v-model="person.last_name" :disabled="remove">
                            </div>
                            <div class="form-group">
                                <label>Birthday</label>
                                <input type="date" class="form-control mb-2" placeholder="Last name" v-model="person.birthday" :disabled="remove">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn"
                                    v-bind:class="{ 'btn-danger': remove, 'btn-info': add, 'btn-success': edit }"
                                    @click="remove? removePerson(person.id) : addPerson()">
                                {{ edit? 'Update ': remove? 'Remove ': add? 'Add ': ''}} person
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- pagination -->
            <nav class="mt-2 mb-2 pl-0 container-fluid" v-if="people.length != 0">
                <ul class="pagination">
                    <li class="page-item" @click="fetchPeople(pagination.first_link)" >
                        <a class="page-link" href="#">First</a>
                    </li>

                    <li class="page-item" v-bind:class="[{ disabled: !pagination.prev_link }]" @click="pagination.prev_link && fetchPeople(pagination.prev_link)" >
                        <a class="page-link" href="#">Previous</a>
                    </li>
                    <li class="page-item disabled">
                        <a class="page-link text-dark" href="#">Page {{ pagination.current_page }} of {{ pagination.last_page }}</a>
                    </li>
                    <li class="page-item" v-bind:class="[{ disabled: !pagination.next_link }]"  @click="pagination.next_link && fetchPeople(pagination.next_link)">
                        <a class="page-link" href="#">Next</a>
                    </li>
                    <li class="page-item" @click="fetchPeople(pagination.last_link)" >
                        <a class="page-link" href="#">Last</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';

    export default {
        data() {
            return {
                people: [],
                person: {
                    id: '',
                    first_name: '',
                    last_name: '',
                    birthday: ''
                },
                pagination: {},
                edit: false,
                remove: false,
                add: false,
                search: '',
                filterAge: false,
                filterAgeType: 'equal',
                filterAgeValue: '',
                successOperation: false,
                errors: [],
            }
        },
        created() {
            this.fetchPeople();
        },

        methods: {
            fetchPeople( api_url ){
                api_url = api_url || '/api/people';

                if ( this.search ) {
                    api_url += ( api_url.match( /[\?]/g ) ? '&' : '?' ) + `q=${this.search}`;
                }

                if ( this.filterAge && this.filterAgeType && this.filterAgeValue ) {
                    const type = this.filterAgeType;
                    const value = parseInt(this.filterAgeValue);

                    const from = moment().subtract((value+1), 'years').format('YYYY-MM-DD');
                    const to = moment().subtract(value, 'years').format('YYYY-MM-DD');

                    api_url += ( api_url.match( /[\?]/g ) ? '&' : '?' ) + `date_from=${from}`;
                    api_url += ( api_url.match( /[\?]/g ) ? '&' : '?' ) + `date_to=${to}`;
                    api_url += ( api_url.match( /[\?]/g ) ? '&' : '?' ) + `type=${type}`;
                }

                fetch( api_url )
                    .then( res => res.json() )
                    .then( res => {
                        this.people = res.data;
                        this.makePagination( res.meta, res.links );
                     })
                    .catch( err => {
                       console.log("err: ", err);
                    });

            },
            makePagination( meta, links ){
                const pagination = {
                    current_page: meta.current_page,
                    last_page: meta.last_page,
                    next_link: links.next,
                    prev_link: links.prev,
                    first_link: links.first,
                    last_link: links.last
                };

                this.pagination = pagination;
            },
            removePerson( id ) {
                fetch(`/api/people/${id}`, { method: 'delete'} )
                    .then( res => res.json())
                    .then( res => {
                        $("[data-dismiss=modal]").trigger({ type: "click" });
                        this.successOperation = true;
                        this.fetchPeople();
                    })
                    .catch( err => {
                        console.log("err: ", err);
                    });

            },
            addPerson() {
                if ( this.validatePerson() ) {
                    const method = this.edit? 'put': 'post';
                    fetch(`/api/people`, {
                        method: method,
                        body: JSON.stringify( this.person ),
                        headers: {
                            'content-type' : 'application/json'
                        }
                    })
                        .then(res => res.json())
                        .then(res => {
                            $("[data-dismiss=modal]").trigger({ type: "click" });
                            this.successOperation = true;
                            this.fetchPeople();
                        })
                        .catch(err => {
                            console.log("err: ", err);
                        })
                }

            },
            validatePerson() {
                this.errors = [];
                if ( !this.person.first_name ) {
                    this.errors.push("First name is required.");
                }
                if ( !this.person.last_name ) {
                    this.errors.push('Last name is required.');
                }

                if ( !this.person.birthday ) {
                    this.errors.push('Birthday required.');
                }

                if ( this.errors.length ) {
                    return false;
                }

                return true;
            },
             fetchPerson( id, operation ) {
                if (operation == 'edit') {
                    this.edit = true;
                    this.remove = false;
                    this.add = false;
                } else {
                    this.edit = false;
                    this.remove = true;
                    this.add = false;
                }

                fetch(`/api/people/${id}`)
                    .then( res => res.json() )
                    .then( res => {
                        res = res.data;
                        console.log(res)
                        this.person.first_name = res.first_name;
                        this.person.last_name = res.last_name;
                        this.person.birthday = res.birthday;
                        this.person.id = res.id;
                    })
                    .catch(err => {
                        console.log("err: ", err);
                    });
            },
            showAddPersonModal() {
                this.edit = false;
                this.remove = false;
                this.add = true;

                this.person.first_name = '';
                this.person.last_name = '';
                this.person.birthday = '';
                this.person.id = '';
            },
            calculateAge(date) {
                date = new Date(date);

                const diff_ms = Date.now() - date.getTime();
                const age_dt = new Date(diff_ms);

                const age = age_dt.getUTCFullYear() - 1970;
                if ( age <= 0) {
                    return 0
                } else {
                    return Math.abs(age);s
                }
            },
            debounceFilterAge: _.debounce(function () {
                this.fetchPeople();
            }, 500),
            debounceSearchInput: _.debounce(function () {
                this.fetchPeople();
            }, 500),
        },

        watch: {
            search: function () {
                this.debounceSearchInput();
            },
            filterAgeValue: function () {
                this.debounceFilterAge();
            },
            filterAgeType: function () {
                this.debounceFilterAge();
            },
            filterAge: function () {
                if (!this.filterAge) {
                    this.filterAgeType = 'equal';
                    this.filterAgeValue = '';
                }
            },
            successOperation: function () {
                if ( this.successOperation ) {
                    setTimeout(() => {
                        this.successOperation = false;
                    }, 4000);
                }
            },
            errors: function () {
                if ( this.errors.length ) {
                    setTimeout(() => {
                        this.errors = [];
                    }, 3000);
                }
            }
        },
    }
</script>
