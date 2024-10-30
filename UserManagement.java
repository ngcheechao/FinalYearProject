public class UserManagement {
    private UserDAO userDAO;

    public UserManagement() {
        this.userDAO = new UserDAO(); // Initialize UserDAO
    }

    // Function to create a new user account
    public void createAccount(String username, String email, String password) {
        // Check if the email is already in use
        if (userDAO.getUserByEmail(email) != null) {
            System.out.println("Email already exists. Please use a different email.");
            return;
        }

        // Create a new user and add to the database
        User user = new User(username, password, email, false); // false for regular users
        userDAO.createUser(user);
    }

    // Function to log in a user
    public boolean login(String email, String password) {
        User user = userDAO.getUserByEmail(email);
        if (user != null && user.getPassword().equals(password)) {
            System.out.println("Login successful!");
            return true;
        } else {
            System.out.println("Login failed. Check your credentials.");
            return false;
        }
    }

    // Function to delete a user account
    public void deleteAccount(String email) {
        userDAO.deleteUserByEmail(email);
        System.out.println("Account deleted successfully.");
    }
}
