<?php

namespace Dorcas\ModulesFinance\Http\Controllers;

use App\Http\Controllers\Controller;
use PDF;
use Illuminate\Http\Request;
use Dorcas\ModulesFinance\Models\ModulesFinance;
use App\Dorcas\Hub\Utilities\UiResponse\UiResponse;
use App\Http\Controllers\HomeController;
use Hostville\Dorcas\Sdk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use League\Csv\Reader;
//use App\Dorcas\Support\CreatesFinanceReports;

class ModulesFinanceController extends Controller {

    //use CreatesFinanceReports;

    public function __construct()
    {
        parent::__construct();
        $this->data = [
            'page' => ['title' => config('modules-finance.title')],
            'header' => ['title' => config('modules-finance.title')],
            'selectedMenu' => 'modules-finance',
            'submenuConfig' => 'navigation-menu.modules-finance.sub-menu',
            'submenuAction' => ''
        ];
    }

    public function index()
    {
    	$this->data['availableModules'] = HomeController::SETUP_UI_COMPONENTS;
    	return view('modules-finance::index', $this->data);
    }



    /**
     * @param Request   $request
     * @param Sdk       $sdk
     * @param string    $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function accounts(Request $request, Sdk $sdk, string $id = null)
    {

        $this->data['page']['title'] .= ' &rsaquo; Accounts';
        $this->data['header']['title'] = 'Accounts Manager';
        $this->data['selectedSubMenu'] = 'finance-accounts';
        $this->data['submenuAction'] = '<a id="create_subaccount" href="#" v-on:click.prevent="createSubAccount" class="btn btn-primary btn-block">Add SubAccount</a>';


        $this->setViewUiResponse($request);
        $accounts = $this->getFinanceAccounts($sdk);
        $mode = 'topmost';
        if (!empty($id)) {
            $mode = 'sub_accounts';
            $baseAccount = $accounts->where('id', $id)->first();
            # get the base account
            if (empty($baseAccount)) {
                abort(500, 'Something went wrong while loading the page.');
            }
            $accounts = $accounts->filter(function ($account) use ($id) {
                if (empty($account->parent_account) || empty($account->parent_account['data'])) {
                    return false;
                }
                return $account->parent_account['data']['id'] === $id;
            });
            $this->data['baseAccount'] = $baseAccount;
            $this->data['header']['title'] .= ' - ' . $baseAccount->display_name;
            
        } elseif (!empty($accounts)) {
            $accounts = $accounts->filter(function ($account) {
                return empty($account->parent_account) || empty($account->parent_account['data']);
            });
        }
        $this->data['mode'] = $mode;

        $this->data['accounts'] = !empty($accounts) ? $accounts->values() : collect([]);

        return view('modules-finance::accounts', $this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function accounts_create(Request $request, Sdk $sdk)
    {
        $company = $request->user()->company(true, true);
        # get the company
        try {
            $resource = $sdk->createFinanceResource();
            # the resource
            $payload = $request->request->all();
            foreach ($payload as $key => $value) {
                $resource = $resource->addBodyParam($key, $value);
            }
            $response = $resource->send('post', ['accounts']);
            # send the request
            if (!$response->isSuccessful()) {
                # it failed
                $message = $response->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while adding the new account. '.$message);
            }
            Cache::forget('finance.accounts.'.$company->id);
            $response = (tabler_ui_html_response(['Successfully added the new account.']))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }
        return redirect(url()->current())->with('UiResponse', $response);
    }


    public function reports_generate(Request $request, Sdk $sdk)
    {
        //dd($request);
        try {
            $resource = $sdk->createFinanceResource();
            $resource = $resource->addBodyParam('report_id', $request->report_id)
                                ->addBodyParam('report_date', $request->report_date);
            $response = $resource->send('post', ['reports', 'income_statement']); //$request->report_name
            // sending income  statements  data into income statement template  gives error not  founf
            // sending   income statmen data into balacne  shheet template works
            if (!$response->isSuccessful()) {
                # it failed
                $message = $response->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while fetching response. '.$message);
            }

            file_put_contents('report.pdf', $response->getData());
           

            return response()->download('report.pdf')->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }

        return redirect(url()->current())->with('UiResponse', $response);
    }

    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function accounts_install(Request $request, Sdk $sdk)
    {
        $query = $sdk->createFinanceResource()->send('post', ['install']);
        # make the request
        if (!$query->isSuccessful()) {
            // do something here
            throw new \RuntimeException(
                $query->errors[0]['title'] ?? 'Something went wrong while installing finance for your account.'
            );
        }
        $company = $request->user()->company(true, true);
        # get the company
        Cache::forget('finance.accounts.'.$company->id);
        return response()->json($query->getData());
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function accounts_update(Request $request, Sdk $sdk, string $id)
    {
        $query = $sdk->createFinanceResource();
        $payload = $request->only(['entry_type', 'display_name', 'is_visible']);
        foreach ($payload as $key => $value) {
            $query = $query->addBodyParam($key, $value);
        }
        $response = $query->send('PUT', ['accounts', $id]);
        if (!$response->isSuccessful()) {
            // do something here
            throw new \RuntimeException(
                $query->errors[0]['title'] ?? 'Something went wrong while updating the account information.'
            );
        }
        $company = $request->user()->company(true, true);
        # get the company
        Cache::forget('finance.accounts.'.$company->id);
        return response()->json($response->getData());
    }


    /**
     * @param Request     $request
     * @param Sdk         $sdk
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function entries(Request $request, Sdk $sdk)
    {

        $this->data['page']['title'] .= ' &rsaquo; Entries';
        $this->data['header']['title'] = 'Account Entries';
        $this->data['selectedSubMenu'] = 'finance-entries';
        $this->data['submenuAction'] = '
            <div class="dropdown"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions</button>
                <div class="dropdown-menu">
                <a href="#" class="dropdown-item" v-on:click.prevent="setDefaultEntry()">Add a New Entry</a>
                <a href="#" data-toggle="modal" data-target="#entries-import-modal" class="dropdown-item">Import Entries From CSV</a>
                <a href="#" class="dropdown-item" v-if="accounts.length > 1" v-on:click.prevent="setPresentEntry(\'debit\')">Real Debit/Expense</a>
                <a href="#" class="dropdown-item" v-if="accounts.length > 1" v-on:click.prevent="setFutureEntry(\'debit\')">Future Debit/Expense</a>
                <a href="#" class="dropdown-item" v-if="accounts.length > 1" v-on:click.prevent="setPresentEntry(\'credit\')">Real Credit/Income</a>
                <a href="#" class="dropdown-item" v-if="accounts.length > 1" v-on:click.prevent="setFutureEntry(\'credit\')">Future Credit/Income</a>
                </div>
            </div>
        ';

        $this->setViewUiResponse($request);
        $accounts = $accounts_all = $this->getFinanceAccounts($sdk);
        if (empty($accounts) || $accounts->count() === 0) {
            return redirect(route('finance-accounts'));
        }
        $entriesCount = 0;
        $entries = [];
        $path = ['entries'];
        $this->data['args'] = $request->query->all();
        if ($request->has('account')) {
            # return only the sub-accounts of the selected parent account
            $id = $request->account;
            # get the requested account id
            $baseAccount = $accounts->where('id', $id)->first();
            # get the base account
            if (empty($baseAccount)) {
                abort(500, 'Something went wrong while loading the page.');
            }
            $this->data['addEntryModalTitle'] = '';
            $path = ['accounts', $id, 'entries'];
            $accounts = collect([$baseAccount]);
            $appendName = $baseAccount->display_name;
            if (!empty($baseAccount->parent_account)) {
                $this->data['addEntryModalTitle'] .= $baseAccount->parent_account['data']['display_name'] . ' > ';
                $appendName .= ' (' . $baseAccount->parent_account['data']['display_name'].')';
            }
            $this->data['header']['title'] .= ' - ' . $appendName;
            $this->data['addEntryModalTitle'] .= $baseAccount->display_name;
            
        }

        $query = $sdk->createFinanceResource()->addQueryArgument('include', 'account')->addQueryArgument('limit', 1)->send('get', $path);
        if ($query->isSuccessful()) {
            $entries = $query->data ?? [];
            $entriesCount = $query->meta['pagination']['total'] ?? 0;
        }

        $this->data['entries'] = $entries;
        $this->data['entriesCount'] = $entriesCount;
        $this->data['accounts'] = $accounts->values();
        $this->data['accounts_all'] = $accounts_all->values();
        return view('modules-finance::entries', $this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function entries_create(Request $request, Sdk $sdk)
    {
        $this->validate($request, [
            'account' => 'required|string',
            'import_file' => 'required_if:action,save_entries|file|max:5120',
            'currency' => 'required_if:action,save_entry|string|size:3',
            'amount' => 'required_if:action,save_entry|numeric',
            'memo' => 'nullable|string',
            'created_at' => 'nullable|date_format:Y-m-d',
        ]);
        # validate the request
        $action = $request->input('action');
        try {
            $resource = $sdk->createFinanceResource();
            # the resource
            switch ($action) {
                case 'save_entries':
                    $file = $request->file('import_file');
                    if (empty($file)) {
                        throw new \RuntimeException('You need to upload a CSV containing the entries.');
                    }
                    $csv = Reader::createFromPath($file->getRealPath(), 'r');
                    $csv->setHeaderOffset(0);
                    $records = $csv->getRecords(['currency', 'amount', 'memo', 'source_type', 'source_info', 'created_at']);
                    $entries = [];
                    foreach ($records as $record) {
                        $entries[] = $record;
                    }
                    $resource->addBodyParam('account', $request->input('account'));
                    $resource->addBodyParam('entries', $entries);
                    $response = $resource->send('post', ['entries', 'bulk']);
                    # send the request
                    if (!$response->isSuccessful()) {
                        # it failed
                        $message = $response->errors[0]['title'] ?? '';
                        throw new \RuntimeException('Failed while adding the accounting entries. '.$message);
                    }
                    $response = (tabler_ui_html_response(['Successfully added new accounting entries.']))->setType(UiResponse::TYPE_SUCCESS);
                    break;
                case 'save_entry':
                default:
                    $payload = $request->request->all();
                    foreach ($payload as $key => $value) {
                        $resource = $resource->addBodyParam($key, $value);
                    }
                    $response = $resource->send('post', ['entries']);
                    # send the request
                    if (!$response->isSuccessful()) {
                        # it failed
                        $message = $response->errors[0]['title'] ?? '';
                        throw new \RuntimeException('Failed while adding the accounting entry. '.$message);
                    }
                    $response = (tabler_ui_html_response(['Successfully added new accounting entry.']))->setType(UiResponse::TYPE_SUCCESS);
                    break;
            }
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }
        $args = $request->query->all();
        return redirect(url()->current() . '?' . http_build_query($args))->with('UiResponse', $response);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function entries_show(Request $request, Sdk $sdk, string $id)
    {
        /*$this->data['breadCrumbs']['crumbs'][1]['isActive'] = false;
        $this->data['breadCrumbs']['crumbs'][] = [
            'text' => 'Confirm Entry',
            'href' => route('apps.finance.entry.confirmation', [$id]),
            'isActive' => true
        ];*/
        # adjust the breadcrumbs for the page
        $accounts = $this->getFinanceAccounts($sdk);
        # get all accounts - first
        if (empty($accounts) || $accounts->count() === 0) {
            return redirect(route('apps.finance'));
        }
        $this->data['accounts'] = $accounts->filter(function ($account) {
            return empty($account->parent_account) && empty($account->parent_account['data']);
        });
        $query = $sdk->createFinanceResource()->addQueryArgument('include', 'account')
                                                ->send('GET', ['entries', $id]);
        # get the response
        if (!$query->isSuccessful()) {
            return redirect()->route('apps.finance.entries');
        }
        $this->data['entry'] = $entry = $query->getData(true);
        # get the entry information
        return view('modules-finance::entries-confirmation', $this->data);
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function entries_search(Request $request, Sdk $sdk)
    {
        $search = $request->query('search', '');
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 10);
        # get the request parameters
        $path = ['entries'];
        if ($request->has('account')) {
            $id = $request->account;
            # get the requested account id
            $path = ['accounts', $id, 'entries'];
        }
        $query = $sdk->createFinanceResource();
        $query = $query->addQueryArgument('include', 'account')
                        ->addQueryArgument('limit', $limit)
                        ->addQueryArgument('page', get_page_number($offset, $limit));
        if (!empty($search)) {
            $query = $query->addQueryArgument('search', $search);
        }
        $response = $query->send('get', $path);
        # make the request
        if (!$response->isSuccessful()) {
            // do something here
            throw new RecordNotFoundException($response->errors[0]['title'] ?? 'Could not find any matching entries.');
        }
        $this->data['total'] = $response->meta['pagination']['total'] ?? 0;
        # set the total
        $this->data['rows'] = $response->data;
        # set the data
        return response()->json($this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function entries_delete(Request $request, Sdk $sdk, string $id)
    {
        $query = $sdk->createFinanceResource()->send('DELETE', ['entries', $id]);
        if (!$query->isSuccessful()) {
            // do something here
            throw new RecordNotFoundException($query->errors[0]['title'] ?? 'Could not delete the selected entry.');
        }
        return response()->json($query->getData());
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function entries_update(Request $request, Sdk $sdk, string $id)
    {
        $payload = $request->only(['account']);
        # the data to be sent
        $query = $sdk->createFinanceResource()->relationships(['account']);
        foreach ($payload as $key => $value) {
            $query = $query->addBodyParam($key, $value);
        }
        $response = $query->send('PUT', ['entries', $id]);
        if (!$response->isSuccessful()) {
            // do something here
            throw new RecordNotFoundException($response->errors[0]['title'] ?? 'Could not update the details on the selected entry.');
        }
        return response()->json($response->getData());
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reports(Request $request, Sdk $sdk)
    {
        $this->data['page']['title'] .= ' &rsaquo; Reports';
        $this->data['header']['title'] = 'Reports Manager';
        $this->data['selectedSubMenu'] = 'finance-reports';
        $this->data['submenuAction'] = '<a href="'.route('finance-reports-configure').'" class="btn btn-primary btn-block">Configure A Report</a>';

        $this->setViewUiResponse($request);
        $accounts = $this->getFinanceAccounts($sdk);
        if (empty($accounts) || $accounts->count() === 0) {
            return redirect(route('finance-accounts'));
        }
        $this->data['configurations'] = $this->getFinanceReportConfigurations($sdk);
        $this->data['reportToken'] = $sdk->getAuthorizationToken();
        # get the configured reports

        //create report labels if not set
        $company = $request->user()->company(true, true);
        # get the company information
        $configuration = !empty($company->extra_data) ? $company->extra_data : [];
        $financeConfig = !empty($configuration['financeConfig']) ? $configuration['financeConfig'] : [];


        if (count($financeConfig) < 1) {
            // lets create sales config
            $configuration['financeConfig'] = [];
            $configuration['financeConfig']['report_labels'] = [
                'income_statement' => [
                    "2cb65f73-15b3-739c-b76f-4f81fd2074b8" => [
                        "id" => "2cb65f73-15b3-739c-b76f-4f81fd2074b8",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Total Revenue",
                        "tag" => "revenue",
                        "multiple_accounts" => "no"
                    ],
                    "3c564b0b-2db1-e2ad-d899-a10996862bc7" => [
                        "id" => "3c564b0b-2db1-e2ad-d899-a10996862bc7",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Cost Of Goods Sold",
                        "tag" => "cogs",
                        "multiple_accounts" => "no"
                    ],
                    "b7de5721-ecad-0549-d0ab-28daf49fee42" => [
                        "id" => "b7de5721-ecad-0549-d0ab-28daf49fee42",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Operating Expenses",
                        "tag" => "expenses",
                        "multiple_accounts" => "no"
                    ],
                    "94378f8e-273d-4b3b-af58-905358a1323b" => [
                        "id" => "94378f8e-273d-4b3b-af58-905358a1323b",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Income Tax Expense",
                        "tag" => "tax",
                        "multiple_accounts" => "no"
                    ]
                ],
                'balance_sheet' => [
                    "8ea5edca-1c9a-263b-bbea-35d0b9c0917f" => [
                        "id" => "8ea5edca-1c9a-263b-bbea-35d0b9c0917f",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Assets",
                        "tag" => "assets",
                        "multiple_accounts" => "yes"
                    ],
                    "e3fefaf7-63b1-0598-36b9-08cdfd8845dc" => [
                        "id" => "e3fefaf7-63b1-0598-36b9-08cdfd8845dc",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Liabilities & Owners Equity",
                        "tag" => "liability_equity",
                        "multiple_accounts" => "yes"
                    ]
                ]

            ];
            $saveQuery = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                                ->send('post');
                                              
            # send the request
            if (!$saveQuery->isSuccessful()) {
                throw new \RuntimeException('Failed while setting Finance Report Labels. Please try again.');
            }
        }
     
      
        return view('modules-finance::reports', $this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function reports_show_manager(Request $request, Sdk $sdk, string $id)
    {
        $this->data['page']['title'] .= ' &rsaquo; Reports';
        $this->data['header']['title'] = 'Reports Manager';
        $this->data['selectedSubMenu'] = 'finance-reports';
        $this->data['submenuAction'] = '<a href="'.route('finance-reports-configure').'" class="btn btn-primary btn-block">Configure A Report</a>';

        $this->setViewUiResponse($request);
        $this->data['page']['title'] = 'Reports Manager';
        $reports = $this->getFinanceReportConfigurations($sdk);
        if (empty($reports)) {
            return redirect()->route('finance-reports');
        }
        $this->data['report'] = $report = $reports->where('id', $id)->first();
        # find the report
        if (empty($report)) {
            abort(404, 'Page not found');
        }
        //dd($report);
        $this->data['page']['header']['title'] = $report->display_name;
        return view('modules-finance::reports-manager', $this->data);
    }

    public function reports_configure(Request $request, Sdk $sdk, string $id = null)
    {
        $this->data['page']['title'] .= ' &rsaquo; Reports Configuration';
        $this->data['header']['title'] = 'Reports Configuration';
        $this->data['selectedSubMenu'] = 'finance-reports';
        $this->data['submenuAction'] = '';
        
        $this->setViewUiResponse($request);
        $accounts = $this->getFinanceAccounts($sdk);
      
        if (empty($accounts) || $accounts->count() === 0) {
            return redirect(route('finance-accounts'));
        }

        //create report labels if not set
        $company = $request->user()->company(true, true);
     

        # get the company information
        $configuration = !empty($company->extra_data) ? $company->extra_data : [];
       
        $financeConfig = !empty($configuration['financeConfig']) ? $configuration['financeConfig'] : [];
       

        if (count($financeConfig) <  1) {
            return redirect(route('finance-accounts'));
        }

        // show parent accounts data
        $parent_accounts = $accounts->filter(function ($account) {
            return empty($account->parent_account) || empty($account->parent_account['data']);
        })->values();
       

        $visible_parents = $accounts->filter(function ($account) {
            return (empty($account->parent_account) || empty($account->parent_account['data'])) && $account->is_visible;
        })->values();


        $parent_accounts_ids = $parent_accounts->map(function ($account) {
            return $account->id;
        })->values()->toArray();


        $child_accounts = $accounts->filter(function ($account) use($parent_accounts_ids) {
            return !empty($account->parent_account) && !empty($account->parent_account['data']) && in_array($account->parent_account['data']['id'], $parent_accounts_ids);
        })->values();

        $child_accounts_select =  [];

        foreach ($child_accounts as $key => $value) {
            $child_accounts_select[] = ["text" => $value->display_name, "value" => $value->id];
        }

        $this->data['accounts'] =  $parent_accounts;
        $this->data['accounts_parents'] =  $visible_parents;
        $this->data['accounts_children'] =  $child_accounts;
        $this->data['accounts_children_select'] =  $child_accounts_select;
        //$this->data['accounts_parents'] =  $parent_accounts_ids;
        # set the accounts to be displayed for selection

        $configured = $this->getFinanceReportConfigurations($sdk);
    //    dd($configured);

//        dd( $this->getFinanceReportConfigurations($sdk));
        $this->data['configurations'] =  $configured;

        if (!empty($id)) {
            $report = $configured->where('id', $id)->first();

            $this->data['report'] = $report ?: null;
        }

        $this->data['reportLabels'] = $this->report_label_get($request,$sdk);

        return view('modules-finance::reports-configure', $this->data);
    }
    
    /**
     * @param Request     $request
     * @param Sdk         $sdk
     * @param string|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reports_configure_post(Request $request, Sdk $sdk, string $id = null)
    {
        $this->validate($request, [
            'report' => 'required|string|in:balance_sheet,income_statement',
            'configured_report_title' => 'required|string'
        ]);
        /*,
            'accounts' => 'required|array',
            'accounts.*' => 'required|string'*/
        # validate the request
        try {
            $company = $request->user()->company(true, true);
            # get the company information
          

            $labels = $this->report_label_get($request, $sdk);
            
            
            /*$query = $sdk->createFinanceResource()->addBodyParam('report_name', $request->report)
                                                    ->addBodyParam('accounts', $request->accounts);
            if (empty($id)) {
                $query = $query->send('POST', ['reports', 'configure']);
            } else {
                $query = $query->send('PUT', ['reports', 'configure', $id]);
            }
            # send the request
            if (!$query->isSuccessful()) {
                throw new \RuntimeException('Failed while saving the report configuration. Please try again.');
            }*/

               /*'balance_sheet' => [
                    "8ea5edca-1c9a-263b-bbea-35d0b9c0917f" => [
                        "id" => "8ea5edca-1c9a-263b-bbea-35d0b9c0917f",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Assets"
                    ],
                    "e3fefaf7-63b1-0598-36b9-08cdfd8845dc" => [
                        "id" => "e3fefaf7-63b1-0598-36b9-08cdfd8845dc",
                        "type" => "default",
                        "accounts" => [],
                        "title" => "Liabilities & Owners Equity"
                    ]
                ]*/

            $configuration = !empty($company->extra_data) ? $company->extra_data : [];
            $financeConfig = !empty($configuration['financeConfig']) ? $configuration['financeConfig'] : [];
       
            // lets update finance config
            $reportLabels = !empty($financeConfig['report_labels']) ? $financeConfig['report_labels'] : [];
            $reportLabel = $reportLabels[$request->report];
           
           
            //loop config update
            foreach ($reportLabel as $key => $value) {
                $label_post_id = "label_box-" . $key;
                $reportLabel[$key]["accounts"] = $request->$label_post_id;
            }

            $configuration['financeConfig']['report_labels'][$request->report] = $reportLabel;
            $saveQuery = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                                ->send('post');
                                            
            # send the request
            if (!$saveQuery->isSuccessful()) {
                throw new \RuntimeException('Failed while updating Sales Product Variant Types. Please try again.');
            }


            Cache::forget('finance.report_configurations.'.$company->id);
            # forget the cache data
            $message = ['Successfully saved the configuration for '.$request->configured_report_title];
            $response = (tabler_ui_html_response($message))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }
        return redirect(url()->current())->with('UiResponse', $response);
    }


    public function report_label_get(Request $request, Sdk $sdk)
    {
        $company = $request->user()->company(true, true);
        # get the company information
        $financeConfig = !empty($company->extra_data['financeConfig']) ? $company->extra_data['financeConfig'] : [];
        $reportLabels = !empty($financeConfig) ? $financeConfig['report_labels'] : [];
        return $reportLabels;
        //return response()->json($variantTypes);
    }

    public function report_label_set(Request $request, Sdk $sdk)
    {

        $company = $request->user()->company(true, true);
        # get the company information
        $configuration = !empty($company->extra_data) ? $company->extra_data : [];
        $salesConfig = !empty($configuration['salesConfig']) ? $configuration['salesConfig'] : [];

        // lets update sales config
        $variantTypes = !empty($salesConfig['variant_types']) ? $salesConfig['variant_types'] : [];
        array_push($variantTypes, $request->input('variant_type'));
        $configuration['salesConfig']['variant_types'] = $variantTypes;
        $saveQuery = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                            ->send('post');
        # send the request
        if (!$saveQuery->isSuccessful()) {
            throw new \RuntimeException('Failed while updating Sales Product Variant Types. Please try again.');
        }

        //$newTypes = $saveQuery->getData();
        return response()->json($variantTypes);
    }

    public function report_label_remove(Request $request, Sdk $sdk)
    {
        $company = $request->user()->company(true, true);
        # get the company information
        $configuration = !empty($company->extra_data) ? $company->extra_data : [];
        $salesConfig = !empty($configuration['salesConfig']) ? $configuration['salesConfig'] : [];

        // lets update sales config
        $reportLabels = !empty($salesConfig['variant_types']) ? $salesConfig['variant_types'] : [];

        if (false !== $key = array_search($request->input('variant_name'), $variantTypes)) {
          unset($variantTypes[$key]);
        }

        $configuration['salesConfig']['variant_types'] = $variantTypes;
        $saveQuery = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                            ->send('post');
        # send the request
        if (!$saveQuery->isSuccessful()) {
            throw new \RuntimeException('Failed while updating Sales Product Variant Types. Please try again.');
        }

        //$newTypes = $saveQuery->getData();
        return response()->json($variantTypes);
    }


    public function generateInvoice(Sdk $sdk, string $id)
    {
        $accounts = $this->getFinanceAccounts($sdk);
        
        # get all accounts - first
        if (empty($accounts) || $accounts->count() === 0) {
            return redirect(route('apps.finance'));
        }
        $this->data['accounts'] = $accounts->filter(function ($account) {
            return empty($account->parent_account) && empty($account->parent_account['data']);
        });
    
        $query = $sdk->createFinanceResource()->addQueryArgument('include', 'account')
                                                ->send('GET', ['entries/generate_invoice', $id]);

        # get the response
        if (!$query->isSuccessful()) {
            return redirect()->route('apps.finance.entries');
        }

    
        $this->data['entry'] = $entry = $query->getData(true);
        
        $invoiceData = $query->getData();
        $id = $invoiceData['id'];
        // dd( $invoiceData['account']['data']['parent_account']['data']['display_name']);

        #load information into pdf
    
        $pdf = PDF::loadView('modules-finance::pdfview', compact('invoiceData' ,'id'));

        return $download =  $pdf->download($invoiceData['account']['data']['parent_account']['data']['display_name'].'_report.pdf');
      
 
    }



    public function reports_create(Request $request, Sdk $sdk)
    {
    
    }

//    public function getFinanceReportConfigurations($sdk)
//    {
//        dd($sdk);
//    }

    public function creator(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string'
        ]);
        # validate the request
    }


}