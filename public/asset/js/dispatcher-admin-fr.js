'use strict';

class DispatcherPanel extends React.Component {
    componentWillMount() {
        this.setState({
            listContent: 'dispatch-map'
        });
    }

    handleUpdateBody(body) {
        console.log('Body Update Called', body);
        this.setState({
            listContent: body
        });
    }

    handleUpdateFilter(filter) {
        console.log('Filter Update Called', this.state.listContent);
        if(filter == 'all'){
            this.setState({
                listContent: 'dispatch-map'
            });
        }else if(filter == 'assigned'){
            this.setState({
                listContent: 'dispatch-assigned'
            });
        }else if(filter == 'cancelled'){
            this.setState({
                listContent: 'dispatch-cancelled'
            });
        }else if(filter == 'return'){
            this.setState({
                listContent: 'dispatch-return'
            });
        }else{
            this.setState({
                listContent: 'dispatch-map'
            });
        }
    }

    handleRequestShow(trip, event) {
        console.log('Show Request', trip);
        if(trip.status == 'CANCELLED') {
            this.setState({
                listContent: 'dispatch-cancelled',
                trip: trip
            });
        } else {
            if(trip.current_provider_id == 0) {
                this.setState({
                    listContent: 'dispatch-assign',
                    trip: trip
                });

            } else {
                this.setState({
                    listContent: 'dispatch-map',
                    trip: trip
                });
            }
        }
        
        ongoingInitialize(trip);
    }

    handleAutoRequest(trip) {
        if(trip.status == 'SEARCHING' && trip.current_provider_id != 0) {
            this.setState({
                listContent: 'dispatch-map',
                trip: trip
            });
            $('.notification').remove();
            $('.container-fluid').first().before('<div class="alert alert-danger notification"><button type="button" class="close" data-dismiss="alert">×</button><p style="margin-top:10px;">La course est attribué automatiquement. Vous ne pouvez pas attribuer manuellement des chauffeurs.</p></div>');
            setTimeout(function() { $('.notification').fadeOut('fast', 'linear', function() { $('.notification').delay(5000).remove(); }); }, 5000);
        }
        
    }

    handleRequestCancel(argument) {
        this.setState({
            listContent: 'dispatch-map'
        });
    }

    render() {

        let listContent = null;

        // console.log('DispatcherPanel', this.state.listContent);

        switch(this.state.listContent) {
            case 'dispatch-create':
                listContent = <div className="col-md-4">
                        <DispatcherRequest completed={this.handleRequestShow.bind(this)} cancel={this.handleRequestCancel.bind(this)} />
                    </div>;
                break;
            case 'dispatch-map':
                listContent = <div className="col-md-4">
                        <DispatcherList clicked={this.handleRequestShow.bind(this)} checked={this.handleAutoRequest.bind(this)} />
                    </div>;
                break;
            case 'dispatch-assigned':
                listContent = <div className="col-md-4">
                        <DispatcherAssignedList />
                    </div>;
                break;
            case 'dispatch-cancelled':
                listContent = <div className="col-md-4">
                        <DispatcherCancelledList clicked={this.handleRequestShow.bind(this)} />
                    </div>;
                break;
            case 'dispatch-assign':
                listContent = <div className="col-md-4">
                        <DispatcherAssignList trip={this.state.trip} />
                    </div>;
                break;
        }

        return (
            <div className="container-fluid">
                <h4>Dispatcher</h4>

                <DispatcherNavbar body={this.state.listContent} updateBody={this.handleUpdateBody.bind(this)} updateFilter={this.handleUpdateFilter.bind(this)}/>

                <div className="row">
                    { listContent }

                    <div className="col-md-8">
                        <DispatcherMap body={this.state.listContent} />
                    </div>
                </div>
            </div>
        );

    }
};

class DispatcherNavbar extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            body: 'dispatch-map',
            selected:''
        };
    }

    filter(data) {
        console.log('Navbar Filter', data);
        this.setState({selected  : data})
        this.props.updateFilter(data);
    }

    handleBodyChange() {
        // console.log('handleBodyChange', this.state);
        if(this.props.body != this.state.body) {
            this.setState({
                body: this.props.body
            });
        }

        if(this.state.body == 'dispatch-map') {
            this.props.updateBody('dispatch-create');
            this.setState({
                body: 'dispatch-create'
            });
        }else if(this.state.body == 'dispatch-assigned') {
            this.props.updateBody('dispatch-map');
            this.setState({
                body: 'dispatch-assigned'
            });
        }else if(this.state.body == 'dispatch-cancelled') {
            this.props.updateBody('dispatch-map');
            this.setState({
                body: 'dispatch-cancelled'
            });
        } else {
            this.props.updateBody('dispatch-map');
            this.setState({
                body: 'dispatch-map'
            });
        }
    }

    isActive(value){
        return 'nav-item '+((value===this.state.selected) ?'active':'');
    }

    render() {
        return (
            <nav className="navbar navbar-light bg-white b-a mb-2">
                <button className="navbar-toggler hidden-md-up" 
                    data-toggle="collapse"
                    data-target="#process-filters"
                    aria-controls="process-filters"
                    aria-expanded="false"
                    aria-label="Toggle Navigation"></button>
                

                <ul className="nav navbar-nav float-xs-right">
                    <li className="nav-item">
                        <button type="button" 
                            onClick={this.handleBodyChange.bind(this)} 
                            className="btn btn-success btn-md label-right b-a-0 waves-effect waves-light">
                            <span className="btn-label"><i className="ti-plus"></i></span>
                            PRENDRE UNE COURSE
                        </button>
                    </li>
                </ul>

                <div className="collapse navbar-toggleable-sm" id="process-filters">
                    <ul className="nav navbar-nav dispatcher-nav">
                        <li className={this.isActive('all')} onClick={this.filter.bind(this, 'all')}>
                            <span className="nav-link" href="#">Recherche</span>
                        </li>
                        <li className={this.isActive('assigned')} onClick={this.filter.bind(this, 'assigned')}>
                            <span className="nav-link" href="#">Attribué</span>
                        </li>
                        <li className={this.isActive('cancelled')} onClick={this.filter.bind(this, 'cancelled')}>
                            <span className="nav-link" href="#">Annulé</span>
                        </li>
                        
                    </ul>
                </div>
            </nav>
        );
    }
}

class DispatcherList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            1000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() {
        $.get('/admin/dispatcher/trips?type=SEARCHING', function(result) {
            if(result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        this.props.checked(trip);
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>LISTE DES RECHERCHES</b></div>
                <DispatcherListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}

class DispatcherAssignedList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            1000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() {
        $.get('/admin/dispatcher/trips?type=ASSIGNED', function(result) {
            if(result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>LISTE DES ATTRIBUTIONS</b></div>
                <DispatcherAssignedListItem data={this.state.data.data} />
            </div>
        );
    }
}


class DispatcherAssignedListItem extends React.Component {
    appendLeadingZeroes(n) {
        return n<= 9 ? "0" + n : n;
    }

    render() {
        var listItem = function(trip) {
            var date = new Date(trip.updated_at)
            let formatted_date = this.appendLeadingZeroes(date.getDate()) + "-" + this.appendLeadingZeroes((date.getMonth() + 1)) + "-" + date.getFullYear() + " " + this.appendLeadingZeroes(date.getHours()) + ":" + this.appendLeadingZeroes(date.getMinutes()) + ":" + this.appendLeadingZeroes(date.getSeconds())

            return (
                    <div className="il-item" key={trip.id}>
                        <a className="text-black" href="#">
                            <div className="media">
                                <div className="media-body">
                                    <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name} 
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> TERMIN… </span>
                                    : trip.status == 'ASSIGNED' ?
                                        <span className="tag tag-danger pull-right"> ATTRIBU… </span>
                                    : trip.status == 'CANCELLED' ?
                                        <span className="tag tag-danger pull-right"> ANNULÉ </span>
                                    : trip.status == 'SEARCHING' ?
                                        <span className="tag tag-warning pull-right"> RECHERCHE </span>
                                    : trip.status == 'SCHEDULED' ?
                                        <span className="tag tag-primary pull-right"> PLANIFI… </span>
                                    : 
                                        <span className="tag tag-info pull-right"> DÉPOSER </span>
                                    }
                                    </p>
                                    <h6 className="media-heading">De:    {trip.s_address}</h6>
                                    <h6 className="media-heading">À:    {trip.d_address ? trip.d_address : "Non séléctionné"}</h6>
                                    <h6 className="media-heading">Paiement: {trip.payment_mode}</h6>
                                    <h6 className="media-heading">Chauffeur attribué : {trip.provider.first_name} {trip.provider.last_name}</h6>
                                    <span className="text-muted">Attribué le : {formatted_date}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherCancelledList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        window.worldMapInitialize();
        window.Tranxit.TripTimer = setInterval(
            () => this.getTripsUpdate(),
            1000
        );
    }

    componentWillUnmount() {
        clearInterval(window.Tranxit.TripTimer);
    }

    getTripsUpdate() {
        $.get('/admin/dispatcher/trips?type=CANCELLED', function(result) {
            if(result.hasOwnProperty('data')) {
                this.setState({
                    data: result
                });
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
            }
        }.bind(this));
    }

    handleClick(trip) {
        this.props.clicked(trip);
    }

    render() {
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>LISTE DES ANNULATIONS</b></div>
                <DispatcherCancelledListItem data={this.state.data.data} clicked={this.handleClick.bind(this)} />
            </div>
        );
    }
}


class DispatcherCancelledListItem extends React.Component {
    handleClick(trip) {
        this.props.clicked(trip)
    }
    appendLeadingZeroes(n) {
        return n<= 9 ? "0" + n : n;
    }

    render() {
        var listItem = function(trip) {
            var date = new Date(trip.updated_at)
            let formatted_date = this.appendLeadingZeroes(date.getDate()) + "-" + this.appendLeadingZeroes((date.getMonth() + 1)) + "-" + date.getFullYear() + " " + this.appendLeadingZeroes(date.getHours()) + ":" + this.appendLeadingZeroes(date.getMinutes()) + ":" + this.appendLeadingZeroes(date.getSeconds())
            return (
                    <div className="il-item" key={trip.id}  onClick={this.handleClick.bind(this, trip)}>
                        <a className="text-black" href="#">
                            <div className="media">
                                <div className="media-body">
                                    <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name} 
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> TERMIN… </span>
                                    : trip.status == 'CANCELLED' ?
                                        <span className="tag tag-danger pull-right"> ANNULÉ </span>
                                    : trip.status == 'SEARCHING' ?
                                        <span className="tag tag-warning pull-right"> RECHERCHE </span>
                                    : trip.status == 'SCHEDULED' ?
                                        <span className="tag tag-primary pull-right"> PLANIFI… </span>
                                    : 
                                        <span className="tag tag-info pull-right"> DÉPOSER </span>
                                    }
                                    </p>
                                    <h6 className="media-heading">De:    {trip.s_address}</h6>
                                    <h6 className="media-heading">À:    {trip.d_address ? trip.d_address : "Non séléctionné"}</h6>
                                    <h6 className="media-heading">Paiement: {trip.payment_mode}</h6>
                                    <h6 className="media-heading">Raison: {trip.cancel_reason}</h6>
                                    <span className="text-muted">Annulée le: {formatted_date}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherListItem extends React.Component {
    handleClick(trip) {
        this.props.clicked(trip)
    }

    handleCancel(trip, event) {
        event.stopPropagation();
        location.assign("/admin/dispatcher/cancel?request_id="+trip.id);
    }

    appendLeadingZeroes(n) {
        return n<= 9 ? "0" + n : n;
    }
    render() {
        var listItem = function(trip) {
            var date = new Date(trip.created_at)
            let formatted_date = this.appendLeadingZeroes(date.getDate()) + "-" + this.appendLeadingZeroes((date.getMonth() + 1)) + "-" + date.getFullYear() + " " + this.appendLeadingZeroes(date.getHours()) + ":" + this.appendLeadingZeroes(date.getMinutes()) + ":" + this.appendLeadingZeroes(date.getSeconds())
            return (
                    <div className="il-item" key={trip.id} onClick={this.handleClick.bind(this, trip)}>
                        <button className="btn btn-danger" onClick={this.handleCancel.bind(this, trip)} >Annuler la course</button>
                        <a className="text-black" href="#">
                            <div className="media">
                                <div className="media-body">
                                    <p className="mb-0-5">{trip.user.first_name} {trip.user.last_name} 
                                    {trip.status == 'COMPLETED' ?
                                        <span className="tag tag-success pull-right"> TERMIN… </span>
                                    : trip.status == 'CANCELLED' ?
                                        <span className="tag tag-danger pull-right"> ANNULÉ </span>
                                    : trip.status == 'SEARCHING' ?
                                        <span className="tag tag-warning pull-right"> RECHERCHE </span>
                                    : trip.status == 'SCHEDULED' ?
                                        <span className="tag tag-primary pull-right"> PLANIFI… </span>
                                    : 
                                        <span className="tag tag-info pull-right"> DÉPOSER </span>
                                    }
                                    </p>
                                    <h6 className="media-heading">De:    {trip.s_address}</h6>
                                    <h6 className="media-heading">À:    {trip.d_address ? trip.d_address : "Non séléctionné"}</h6>
                                    <h6 className="media-heading">Paiement: {trip.payment_mode}</h6>
                                    <progress className="progress progress-success progress-sm" max="100"></progress>
                                    <span className="text-muted">{trip.current_provider_id == 0 ? "Attribution manuelle" : "Recherche automatique"} : {formatted_date}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                );
        }.bind(this);
        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class DispatcherRequest extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            data: []
        };
    }

    componentDidMount() {

        // Auto Assign Switch
        new Switchery(document.getElementById('provider_auto_assign'));
        
        // Schedule Time Datepicker
        $('#schedule_time').datetimepicker({
            minDate: window.Tranxit.minDate,
            maxDate: window.Tranxit.maxDate,
        });

        // Get Service Type List
        $.get('/admin/service', function(result) {
            this.setState({
                data: result
            });
        }.bind(this));

        // Mount Ride Create Map

        window.createRideInitialize();

        function stopRKey(evt) { 
            var evt = (evt) ? evt : ((event) ? event : null); 
            var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
            if ((evt.keyCode == 13) && (node.type=="text"))  {return false;} 
        } 

        document.onkeypress = stopRKey; 
    }

    createRide(event) {
        console.log(event);
        event.preventDefault();
        event.stopPropagation();
        console.log('Hello', $("#form-create-ride").serialize());
        $.ajax({
            url: '/admin/dispatcher',
            dataType: 'json',
            headers: {'X-CSRF-TOKEN': window.Laravel.csrfToken },
            type: 'POST',
            data: $("#form-create-ride").serialize(),
            success: function(data) {
                if(typeof data.message !== 'undefined') {
                    $('.container-fluid').first().before('<div class="alert alert-danger notification"><button type="button" class="close" data-dismiss="alert">×</button><p style="margin-top:10px;">'+data.message+'</p></div>');
                    setTimeout(function() { $('.notification').fadeOut('fast', 'linear', function() { $('.notification').delay(5000).remove(); }); }, 5000);
                }
                console.log('Accept', data);
                this.props.completed(data);
            }.bind(this)
        });
    }

    cancelCreate() {
        this.props.cancel(true);
    }

    render() {
        return (
            <div className="card card-block" id="create-ride">
                <h3 className="card-title text-uppercase">DÉTAILS DE LA COURSE</h3>
                <form id="form-create-ride" onSubmit={this.createRide.bind(this)} method="POST">
                    <div className="row">
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="first_name">Prénom</label>
                                <input type="text" className="form-control" name="first_name" id="first_name" placeholder="Prénom" required />
                            </div>
                        </div>
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="last_name">Nom</label>
                                <input type="text" className="form-control" name="last_name" id="last_name" placeholder="Nom" required />
                            </div>
                        </div>
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="email">Email</label>
                                <input type="email" className="form-control" name="email" id="email" placeholder="Email" required/>
                            </div>
                        </div>
                        <div className="col-xs-6">
                            <div className="form-group">
                                <label htmlFor="mobile">N° de téléphone</label>
                                <input type="text" className="form-control numbers" name="mobile" id="mobile" placeholder="N° de téléphone" required />
                            </div>
                        </div>
                        <div className="col-xs-12">
                            <div className="form-group">
                                <label htmlFor="s_address">Adresse de prise en charge</label>
                                
                                <input type="text"
                                    name="s_address"
                                    className="form-control"
                                    id="s_address"
                                    placeholder="Adresse de prise en charge"
                                    required></input>

                                <input type="hidden" name="s_latitude" id="s_latitude"></input>
                                <input type="hidden" name="s_longitude" id="s_longitude"></input>
                            </div>
                            <div className="form-group">
                                <label htmlFor="d_address">Adresse de dépôt</label>
                                
                                <input type="text" 
                                    name="d_address"
                                    className="form-control"
                                    id="d_address"
                                    placeholder="Adresse de dépôt"
                                    required></input>

                                <input type="hidden" name="d_latitude" id="d_latitude"></input>
                                <input type="hidden" name="d_longitude" id="d_longitude"></input>
                                <input type="hidden" name="distance" id="distance"></input>
                            </div>
                            <div className="form-group">
                                <label htmlFor="schedule_time">Heure de planification</label>
                                <input type="text" className="form-control" name="schedule_time" id="schedule_time" placeholder="Date" />
                            </div>
                            <div className="form-group">
                                <label htmlFor="note">Note</label>
                                <input type="text" className="form-control" name="note" id="note" placeholder="Note" />
                            </div>
                            <div className="form-group">
                                <label htmlFor="service_types">Type de service</label>
                                <ServiceTypes data={this.state.data} />
                            </div>
                            <div className="form-group">
                                <label htmlFor="estimated" className="estimate_amount">Montant estimé :  <span id="estimated">0€</span></label>
                            </div>
                            <div className="form-group">
                                <label htmlFor="provider_auto_assign">Attribution automatique du chauffeur</label>
                                <br />
                                <input type="checkbox" id="provider_auto_assign" name="provider_auto_assign" className="js-switch" data-color="#f59345" defaultChecked />
                            </div>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-xs-6">
                            <button type="button" className="btn btn-lg btn-danger btn-block waves-effect waves-light" onClick={this.cancelCreate.bind(this)}>
                                ANNULER
                            </button>
                        </div>
                        <div className="col-xs-6">
                            <button id="showbtn" className="btn btn-lg btn-success btn-block waves-effect waves-light" disabled>
                                VALIDER
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        );
    }
};

class DispatcherAssignList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            data: {
                data: []
            }
        };
    }

    componentDidMount() {
        $.get('/admin/dispatcher/providers', { 
            service_type: this.props.trip.service_type_id,
            latitude: this.props.trip.s_latitude,
            longitude: this.props.trip.s_longitude
        }, function(result) {
            console.log('Providers', result);
            if(result) {
                result['data']=result;
                this.setState({
                    data: result
                });
                window.assignProviderShow(result.data, this.props.trip);
            } else {
                this.setState({
                    data: {
                        data: []
                    }
                });
                window.providerMarkersClear();
            }
        }.bind(this));
    }

    render() {
        console.log('DispatcherAssignList - render', this.state.data);
        return (
            <div className="card">
                <div className="card-header text-uppercase"><b>Attribuer un chauffeur</b></div>
                
                <DispatcherAssignListItem data={this.state.data.data} trip={this.props.trip} />
            </div>
        );
    }
}

class DispatcherAssignListItem extends React.Component {
    handleClick(provider) {
        // this.props.clicked(trip)
        console.log('Provider Clicked');
        window.assignProviderPopPicked(provider);
    }
    render() {
        var listItem = function(provider) {
            return (
                    <div className="il-item" key={provider.id} onClick={this.handleClick.bind(this, provider)}>
                        <a className="text-black" href="#">
                            <div className="media">
                                <div className="media-body">
                                    <p className="mb-0-5">{provider.first_name} {provider.last_name}</p>
                                    <h6 className="media-heading">Rating: {provider.rating}</h6>
                                    <h6 className="media-heading">N° de téléphone: {provider.mobile}</h6>
                                    <h6 className="media-heading">Type: {provider.service.service_type.name}</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                );
        }.bind(this);

        return (
            <div className="items-list">
                {this.props.data.map(listItem)}
            </div>
        );
    }
}

class ServiceTypes extends React.Component {
    render() {
        // console.log('ServiceTypes', this.props.data);
        var mySelectOptions = function(result) {
            return <ServiceTypesOption
                    key={result.id}
                    id={result.id}
                    name={result.name} />
        };
        return (
                <select 
                    name="service_type"
                    className="form-control" id="service_type">
                    {this.props.data.map(mySelectOptions)}
                </select>
            )
    }
}

class ServiceTypesOption extends React.Component {
    render() {
        return (
            <option value={this.props.id}>{this.props.name}</option>
        );
    }
};

class DispatcherMap extends React.Component {
    render() {
        return (
            <div className="card my-card">
                <div className="card-header text-uppercase">
                    <b>MAP</b>
                </div>
                <div className="card-body">
                    <div id="map" style={{ height: '450px'}}></div>
                </div>
            </div>
        );
    }
}

ReactDOM.render(
    <DispatcherPanel />,
    document.getElementById('dispatcher-panel')
);