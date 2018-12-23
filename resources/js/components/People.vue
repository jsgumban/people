<template>
    <div>
        <div class="container bg-light mt-4 mb-3 pb-4 pt-4 pl-5 pr-5">
            <button class="btn btn-info float-right mb-3" data-toggle="modal" data-target="#personModal" @click="showAddPersonModal()">
                Add Person
            </button>

            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Search" v-model="search" v-on:input="debounceSearchInput" >
            </div>

            <div class="card card-body mb-4 mt-4" v-for="person in people" v-bind:key="person.id">
                <h4> {{ person.first_name + ' ' + person.last_name }} </h4>
                <h6>Birthday: {{ person.birthday }}</h6>
                <hr>
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <button class="btn btn-block btn-success mb-2" data-toggle="modal" data-target="#personModal" @click="fetchPerson(person.id, 'edit')" >Edit</button>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <button class="btn btn-block btn-danger" data-toggle="modal" data-target="#personModal" @click="fetchPerson(person.id, 'remove')">Delete</button>
                    </div>
                </div>
            </div>

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
                            <input type="text" class="form-control mb-2" placeholder="First name" v-model="person.first_name" :disabled="remove">
                            <input type="text" class="form-control mb-2" placeholder="Last name" v-model="person.last_name" :disabled="remove">
                            <input type="date" class="form-control mb-2" placeholder="Last name" v-model="person.birthday" :disabled="remove">
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

            <nav class="mt-2 mb-2 pl-0 container-fluid">
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
                search: ''
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

                fetch( api_url )
                    .then( res => res.json() )
                    .then( res => {
                        this.people = res.data;
                        this.makePagination( res.meta, res.links );
                     })
                    .catch( err => {
                       console.log("fetch error: ", err);
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
                        this.fetchPeople();
                    })
                    .catch( err => {
                        console.log("remove person err: ", err);
                    });

            },
            addPerson() {
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
                        this.fetchPeople();
                    })
                    .catch(err => {
                        console.log("err: ", err);
                    })
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
            debounceSearchInput: _.debounce(function () {
                this.fetchPeople();
            }, 500)
        },

        watch: {
            search: function () {
                this.debounceSearchInput();
            }
        },
    }
</script>
