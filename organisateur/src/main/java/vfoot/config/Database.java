package vfoot.config;

import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

/**
 * Connexion MySQL unique (singleton).
 *
 * Les identifiants ne sont JAMAIS écrits dans le code. Ordre de priorité :
 *   1. variables d'environnement DB_URL / DB_USER / DB_PASSWORD
 *   2. fichier src/main/resources/config.properties (ignoré par git,
 *      copier config.properties.example)
 *   3. valeurs par défaut XAMPP locales
 */
public class Database {

    private static final String DEFAULT_URL =
        "jdbc:mysql://localhost:3306/efootball_tournament?useSSL=false&serverTimezone=UTC&characterEncoding=UTF-8";

    private static Connection connection;

    private Database() {}

    public static Connection get() {
        if (connection == null) {
            Properties props = loadProperties();
            String url  = setting("DB_URL",      props.getProperty("db.url",      DEFAULT_URL));
            String user = setting("DB_USER",     props.getProperty("db.user",     "root"));
            String pass = setting("DB_PASSWORD", props.getProperty("db.password", ""));
            try {
                connection = DriverManager.getConnection(url, user, pass);
            } catch (SQLException e) {
                throw new RuntimeException("Connexion DB échouée : " + e.getMessage(), e);
            }
        }
        return connection;
    }

    private static Properties loadProperties() {
        Properties props = new Properties();
        try (InputStream in = Database.class.getClassLoader().getResourceAsStream("config.properties")) {
            if (in != null) props.load(in);
        } catch (IOException ignored) {
            // pas de fichier : on garde les valeurs par défaut
        }
        return props;
    }

    private static String setting(String envName, String fallback) {
        String value = System.getenv(envName);
        return (value != null && !value.isEmpty()) ? value : fallback;
    }
}
