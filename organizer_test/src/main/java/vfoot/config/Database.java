package vfoot.config;

import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.util.Properties;

/**
 * Singleton — une seule connexion JDBC partagée dans toute l'application.
 * Equivalent du db() singleton PDO dans test/config.php.
 */
public class Database {

    private static Connection instance;

    private Database() {}

    public static Connection get() {
        if (instance == null) {
            try {
                Properties props = new Properties();
                InputStream in = Database.class
                        .getClassLoader()
                        .getResourceAsStream("config.properties");
                if (in == null) throw new RuntimeException("config.properties introuvable");
                props.load(in);

                instance = DriverManager.getConnection(
                        props.getProperty("db.url"),
                        props.getProperty("db.user"),
                        props.getProperty("db.password")
                );
            } catch (Exception e) {
                throw new RuntimeException("Connexion DB échouée : " + e.getMessage(), e);
            }
        }
        return instance;
    }
}
