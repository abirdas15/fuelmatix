const ApiVersion = '/admin/api/v1'
const ApiRoutes = {
    // Authentication
    Login: ApiVersion + '/auth/login',
    Logout: ApiVersion + '/auth/logout',
    Profile: ApiVersion + '/profile',
    Company: ApiVersion + '/company',
    User: ApiVersion + '/user',
    Dashboard: ApiVersion + '/dashboard',
};

export default ApiRoutes;
