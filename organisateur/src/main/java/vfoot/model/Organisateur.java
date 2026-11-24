package vfoot.model;

public class Organisateur {
    private int    idUser;
    private String username;
    private String email;
    private String passwordHash;

    public int    getIdUser()       { return idUser; }
    public String getUsername()     { return username; }
    public String getEmail()        { return email; }
    public String getPasswordHash() { return passwordHash; }

    public void setIdUser(int idUser)             { this.idUser = idUser; }
    public void setUsername(String username)       { this.username = username; }
    public void setEmail(String email)             { this.email = email; }
    public void setPasswordHash(String hash)       { this.passwordHash = hash; }
}
