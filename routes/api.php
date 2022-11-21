<?php
// Version 260722/1100
use Illuminate\Http\Request;

Route::get('checksubsexpiry','CasterNtripSubscriptionsController@checksubsexpiry');
Route::get('getcaster','CasterDataController@getcaster');
Route::get('testgetcaster','CasterDataController@testgetcaster');
Route::get('disconnectclient','CasterDataController@disconnectclient');
Route::get('casterdealerbranding','CasterDealersController@getdealerbranding');
Route::get('storestats','CasterDataController@storestats');
Route::get('casterloginlink','CasterDealersController@casterloginlink');
	
Route::prefix('auth')->group(function () {
	Route::post('/vuelogin', 'AuthController@vuelogin');
	Route::post('logout', 'AuthController@logout');
	Route::post('reset-password', 'AuthController@sendPasswordResetLink');
    Route::post('resetpassword', 'AuthController@callResetPassword');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
	
	Route::get('casterdatapoll','CasterDataPollController@index');
	Route::post('casterdatapoll','CasterDataPollController@store');
	Route::put('casterdatapoll','CasterDataPollController@store');
	
	Route::post('testsendemail','CasterNtripSubscriptionsController@testsendmail');
	Route::post('sendemail','CasterNtripSubscriptionsController@sendmail');
	Route::post('resendemail','CasterNtripSubscriptionsController@resendemail');
	
	Route::get('castersystem','SystemController@getpollstatus'); 
	
	Route::get('casterrequests','CasterRequestsController@index'); 
    Route::get('casterrequestsRTCM3','CasterRequestsController@indexRTCM3'); 
	Route::get('castersats','CasterRequestsController@indexsats');
	Route::get('casterlog','CasterRequestsController@indexlog');
    Route::get('loggedrovers','CasterRequestsController@loggedrovers');
	Route::get('casterloggedroverstotals','CasterRequestsController@getloggedrovertotals');
	
	Route::get('roversessiondata','CasterSessionsController@roversessiondata');
	Route::get('fixsessioncount','CasterSessionsController@fixsessioncount');
	
	Route::get('getrovers','CasterRequestsController@getrovers');
	Route::post('addtestdata','CasterRequestsController@testdatastore');
	Route::get('castermessages','CasterRequestsController@castermessages');
	Route::get('rovermessages','CasterRequestsController@rovermessages');
	Route::get('rovermessage','CasterRequestsController@rovermessage');
	Route::get('roversessions','CasterRequestsController@roversessions');
	Route::get('lasteventmessages','CasterRequestsController@lasteventmessages');
	Route::get('lasteventrovermessages','CasterRequestsController@lasteventrovermessages');
	Route::get('lasteventbsmessages','BaseStationsController@getlasteventbsmessages');
	Route::get('rtkhistory','CasterRequestsController@rtkhistory');
	Route::get('ntripclienthistory','CasterRequestsController@ntripclienthistory');
	Route::get('subsstats','CasterRequestsController@subsstats');
	
	Route::get('networklogins','CasterRequestsController@networklogins');
	Route::get('networkloginsdata','CasterSessionsController@networkloginsdata');
	Route::get('lastfiveconnections','CasterRequestsController@lastfiveconnections');
	Route::get('datausage','CasterRequestsController@datausage');
	
	Route::get('roverstats','CasterRequestsController@roverstats'); 
	Route::get('rtkstatus','RTKStatusController@index');
	
	Route::get('castersubscriptionsstock','CasterNtripSubscriptionsStockController@index'); 
	Route::delete('castersubscriptionstock/{id}','CasterNtripSubscriptionsStockController@destroy');
    Route::post('castersubscriptionstock','CasterNtripSubscriptionsStockController@store');
    Route::put('castersubscriptionstock','CasterNtripSubscriptionsStockController@store');
	
	Route::get('castersubscriptions','CasterSubscriptionsController@index'); 
	Route::get('castersubscriptionstotals','CasterSubscriptionsController@indextotals'); 
	Route::delete('castersubscription/{id}','CasterSubscriptionsController@destroy');
    Route::post('castersubscription','CasterSubscriptionsController@store');
    Route::put('castersubscription','CasterSubscriptionsController@store');
	
	Route::get('casterntripsubscriptions','CasterNtripSubscriptionsController@index');
	Route::get('casterntripsubscriptionsstocklist','CasterNtripSubscriptionsStockController@getstocksubs');
	Route::get('casterntripsubscriptionsstocklistdealer','CasterNtripSubscriptionsStockController@getstocksubsdealer');
	
	Route::get('casterntripsubscriptionsexpiry','CasterNtripSubscriptionsController@indexexpiry');
	Route::get('casterntripsubscription','CasterNtripSubscriptionsController@getsub');
	Route::get('casterntripsubscriptionsstock','CasterNtripSubscriptionsStockController@indexstock'); 
	Route::get('casterntripsubscriptionsstock2','CasterNtripSubscriptionsStockController@indexstock2'); 
	Route::get('casterntripsubscriptionstotals','CasterNtripSubscriptionsController@gettotals'); 
	Route::get('casterroverssubstotals','CasterNtripSubscriptionsController@getroversubstotals');
	Route::get('casterstocking','CasterNtripSubscriptionsStockController@getstockingtotals');
	Route::delete('casterntripsubscription/{id}','CasterNtripSubscriptionsController@destroy');
    Route::post('updatesubscription','CasterSimmsController@updatesubscription');
	Route::put('updatesubscription','CasterSimmsController@updatesubscription');
	Route::put('casterntripsubscriptioncancel','CasterNtripSubscriptionsController@storecancel');
	
		
	Route::get('getsubstatustypes','CasterNtripSubscriptionsController@getsubstatustypes');
	Route::get('getsubstatustypestotals','CasterNtripSubscriptionsController@getsubstatustypestotals');
	Route::post('addcasterntripsubscriptionstock','CasterNtripSubscriptionsStockController@storestock');
	Route::post('addcasterntripsubscriptionsreseller','CasterNtripSubscriptionsStockController@storestockreseller');
	
	Route::get('casterusers','UsersController@index'); 
	Route::post('casteruserarchive','UsersController@archive');
    Route::post('casteruser','UsersController@store');
    Route::put('casteruser','UsersController@store');
	Route::put('casteruserprofile','UsersController@storeprofile');
	Route::get('checkuserexists','UsersController@checkuserexists'); 

	
	Route::post('casterusercontact','UsersController@storecontact');
    Route::put('casterusercontact','UsersController@storecontact');
	 
	Route::post('casteruserdetail','UsersController@storedetail');
    Route::put('casteruserdetail','UsersController@storedetail');
	
	
	Route::get('castercompanies','CasterCompaniesController@index');
	Route::get('castercompaniestotals','CasterCompaniesController@indextotals');
	Route::get('castercompany','CasterCompaniesController@getcompany');
	Route::post('castercompanyadd','CasterCompaniesController@addcompany');
	Route::post('castercompany','CasterCompaniesController@store');
    Route::put('castercompany','CasterCompaniesController@store');
	Route::post('castercompanyarchive','CasterCompaniesController@archive');
	
	Route::get('casterdealers','CasterDealersController@index'); 
	Route::get('casterdealerstotals','CasterDealersController@indextotals'); 
	Route::get('casterdealer','CasterDealersController@getdealer'); 
	Route::post('casterdealerarchive','CasterDealersController@archive');
	Route::post('casterdealeradd','CasterDealersController@adddealer');
    Route::post('casterdealer','CasterDealersController@store');
    Route::put('casterdealer','CasterDealersController@store');
	
	Route::get('casterbusinesses','CasterBusinessController@index'); 
	Route::post('casterbusinessarchive','CasterBusinessController@archive');
    Route::post('casterbusiness','CasterBusinessController@store');
    Route::put('casterbusiness','CasterBusinessController@store');
	Route::post('casterreselleradd','CasterBusinessController@addreseller');
	Route::post('casterresellerarchive','CasterBusinessController@archive');
	
	Route::get('casterbusinessdealers','CasterBusinessDealerController@index'); 
	Route::post('casterbusinesdealersarchive','CasterBusinessDealerController@archive');
    Route::post('casterbusinessdealer','CasterBusinessDealerController@store');
    Route::put('casterbusinessdealer','CasterBusinessDealerController@store');
	
	
	Route::get('casterdealerdepots','CasterDealerDepotsController@index'); 
	Route::delete('casterdealerdepot/{id}','CasterDealerDepotsController@destroy');
    Route::post('casterdealerdepot','CasterDealerDepotsController@store');
    Route::put('casterdealerdepot','CasterDealerDepotsController@store');
	
	Route::get('castersimms','CasterSimmsController@index');
	Route::get('castersimmusage','CasterSimmsController@simmusage');
	Route::get('castersimmstotals','CasterSimmsController@indextotals');
	Route::get('casterdealerssimmtotals','CasterSimmsController@dealersimmtotals');
	Route::get('castercompanyssimmtotals','CasterSimmsController@companysimmtotals');
	Route::get('casterstatussimmtotals','CasterSimmsController@statussimmtotals'); 
	Route::get('castersimmsstock','CasterSimmsController@indexstock'); 
	Route::delete('castersimm/{id}','CasterSimmsController@destroy');
    Route::post('castersimm','CasterSimmsController@store');
    Route::put('updatesimm','CasterSimmsController@updatesimm');
	Route::post('addsimms','CasterSimmsController@addsimms');
	
	Route::post('bm2mlogin','CasterSimmsController@bm2mlogin');
	Route::get('bm2mgetpackage','CasterSimmsController@bm2mgetpackage');
	Route::get('bm2mgetconnections','CasterSimmsController@bm2mgetconnections');
	Route::get('bm2mgetinactivesimms','CasterSimmsController@bm2mgetinactivesimms');
	Route::get('bm2mgetactivesimms','CasterSimmsController@bm2mgetactivesimms');
	Route::post('bm2msetstatus','CasterSimmsController@bm2msetstatus');
	 
	Route::get('castersimmpackages','CasterSimmPackageController@index'); 
	
	
	Route::get('castersimmstatus','CasterSimmStatusController@index'); 
	Route::get('castersimmstatustotals','CasterSimmStatusController@indextotals'); 
	
	Route::get('castersimmtypes','CasterSimmTypesController@index'); 
	Route::delete('castersimmtype/{id}','CasterSimmTypesController@destroy');
    Route::post('castersimmtype','CasterSimmTypesController@store');
    Route::put('castersimmtype','CasterSimmTypesController@store');
	
	Route::get('casteremails','CasterEmailsController@index'); 
	Route::delete('casteremail/{id}','CasterEmailsController@destroy');
    Route::post('casteremail','CasterEmailsController@store');
    Route::put('casteremail','CasterEmailsController@store');
	
	Route::get('companyrovers','CasterRoversController@indexrovercompany'); 
	
	Route::get('companymachines','CompanyMachinesController@index'); 
    Route::get('companymachine/{id}','CompanyMachinesController@show');
    Route::delete('companymachine/{id}','MachineTypesController@destroy');
    Route::post('companymachine','CompanyMachinesController@store');
    Route::put('companymachine','CompanyMachinesController@store');
	
	Route::get('machinetypes','MachineTypesController@index'); 
    Route::get('machinetype/{id}','MachineTypesController@show');
    Route::delete('machinetype/{id}','MachineTypesController@destroy');
    Route::post('machinetype','MachineTypesController@store');
    Route::put('machinetype','MachineTypesController@store');
	
	Route::get('machinemakers','MachineMakersController@index'); 
    Route::get('machinemaker/{id}','MachineMakersController@show');
    Route::delete('machinemaker/{id}','MachineMakersController@destroy');
    Route::post('machinemaker','MachineMakersController@store');
    Route::put('machinemaker','MachineMakersController@store');
	
	Route::get('machinemodels','MachineModelsController@index'); 
    Route::get('machinemodel/{id}','MachineModelsController@show');
    Route::delete('machinemodel/{id}','MachineModelsController@destroy');
    Route::post('machinemodel','MachineModelsController@store');
    Route::put('machinemodel','MachineModelsController@store');
	
	
	
	Route::get('depts','DeptsController@index'); 
    Route::delete('dept/{id}','DeptsController@destroy');
    Route::post('dept','DeptsController@store');
    Route::put('dept','DeptsController@store');
		
	Route::get('roles','RolesController@index'); 
    Route::delete('role/{id}','RolesController@destroy');
    Route::post('role','RolesController@store');
    Route::put('role','RolesController@store');
	
	Route::get('events','CasterEventsController@index'); 
	Route::delete('event/{id}','CasterEventsController@destroy');
    Route::post('event','CasterEventsController@store');
	Route::post('userevent','CasterEventsController@userevent');
    Route::put('event','CasterEventsController@store');
	
	Route::get('casteremaillogs','CasterEmailsLogController@index'); 
    Route::delete('casteremaillog/{id}','CasterEmailsLogController@destroy');
    Route::post('casteremaillog','CasterEmailsLogController@store');
    Route::put('casteremaillog','CasterEmailsLogController@store');
	
	Route::get('castereventtypes','CasterEventTypesController@index'); 
	
	Route::get('getbasestation','BaseStationsController@getbasestation');
	Route::get('getbasestationstats','BaseStationsController@basestationstats');
	Route::get('basestations','BaseStationsController@index'); 
	Route::get('basestationsdata','BaseStationsController@indexdata'); 
    Route::get('basestation/{id}','BaseStationsController@show');
    Route::delete('basestation/{id}','BaseStationsController@destroy');
    Route::post('basestation','BaseStationsController@store');
    Route::put('basestation','BaseStationsController@store');
	Route::get('getn2yon','BaseStationsController@getn2yo');
	Route::get('getsatplots','BaseStationsController@getsatplots');
	
	
	Route::get('rovers','CasterRoversController@index');
	Route::get('roversmap','CasterRoversController@indexmap'); 
    Route::get('rover/{id}','CasterRoversController@show');
    Route::post('rover','CasterRoversController@store');
    Route::put('rover','CasterRoversController@store');
	Route::post('roverarchive','CasterRoversController@archive');
	Route::post('roveradd','CasterSimmsController@roveradd');
	
	Route::post('photoupload','PhotosController@photoupload');
	Route::post('photorotate','PhotosController@photorotate');
	Route::post('photosetmain','PhotosController@photosetmain');
	
//	Route::get('photos','PhotosController@index'); 
//    Route::get('photo/{id}','PhotosController@show');
//    Route::post('photodelete','PhotosController@photodelete');
//    Route::post('photo','PhotosController@store');
//    Route::put('photo','PhotosController@store');
	
	
	
});