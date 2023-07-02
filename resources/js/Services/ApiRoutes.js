const ApiVersion = '/api/v1.0'
const ApiRoutes = {
    // Authentication
    Login: ApiVersion + '/auth/login',
    Logout: ApiVersion + '/auth/logout',
    Register: ApiVersion + '/auth/register',
    ForgotPassword: ApiVersion + '/auth/forgot',
    ResetPassword: ApiVersion + '/auth/reset/password',
    //Accounts
    CategoryList: ApiVersion + '/category/list',
    CategoryParent: ApiVersion + '/category/parent',
    CategorySave: ApiVersion + '/category/save',
    CategorySingle: ApiVersion + '/category/single',
    CategoryUpdate: ApiVersion + '/category/update',
    //Transaction
    TransactionSave: ApiVersion + '/transaction/save',
    TransactionSingle: ApiVersion + '/transaction/single',
    //Balance Sheet
    BalanceSheetGet: ApiVersion + '/balance-sheet/get',
    //Profit and loss
    ProfitLossGet: ApiVersion + '/profit-and-loss/get',
    //Income statement
    IncomeStatement: ApiVersion + '/income-statement/get',
    //Account payable
    PayableGet: ApiVersion + '/payable/get',
    //Account receivable
    ReceivableGet: ApiVersion + '/receivable/get',
    //Trail Balance
    TrailBalanceGet: ApiVersion + '/trail-balance/get',
    //Ledger
    LedgerGet: ApiVersion + '/ledger/get',
    //Product
    ProductAdd: ApiVersion + '/product/save',
    ProductEdit: ApiVersion + '/product/update',
    ProductDelete: ApiVersion + '/product/delete',
    ProductList: ApiVersion + '/product/list',
    ProductSingle: ApiVersion + '/product/single',
    ProductType: ApiVersion + '/product/type/list',
    ProductDispenser: ApiVersion + '/product/dispenser',
    //Dispenser
    DispenserAdd: ApiVersion + '/dispenser/save',
    DispenserEdit: ApiVersion + '/dispenser/update',
    DispenserDelete: ApiVersion + '/dispenser/delete',
    DispenserList: ApiVersion + '/dispenser/list',
    DispenserSingle: ApiVersion + '/dispenser/single',
    //Nozzle
    NozzleAdd: ApiVersion + '/nozzle/save',
    NozzleEdit: ApiVersion + '/nozzle/update',
    NozzleDelete: ApiVersion + '/nozzle/delete',
    NozzleList: ApiVersion + '/nozzle/list',
    NozzleSingle: ApiVersion + '/nozzle/single',
    //Nozzle Reading
    NozzleReadingAdd: ApiVersion + '/nozzle/reading/save',
    NozzleReadingEdit: ApiVersion + '/nozzle/reading/update',
    NozzleReadingDelete: ApiVersion + '/nozzle/reading/delete',
    NozzleReadingList: ApiVersion + '/nozzle/reading/list',
    NozzleReadingSingle: ApiVersion + '/nozzle/reading/single',
    //Shift Sale
    ShiftSaleAdd: ApiVersion + '/shift/sale/save',
    ShiftSaleEdit: ApiVersion + '/shift/sale/update',
    ShiftSaleDelete: ApiVersion + '/shift/sale/delete',
    ShiftSaleList: ApiVersion + '/shift/sale/list',
    ShiftSaleSingle: ApiVersion + '/shift/sale/single',
    //expense
    ExpenseAdd: ApiVersion + '/expense/save',
    ExpenseEdit: ApiVersion + '/expense/update',
    ExpenseDelete: ApiVersion + '/expense/delete',
    ExpenseList: ApiVersion + '/expense/list',
    ExpenseSingle: ApiVersion + '/expense/single',

};

export default ApiRoutes;
