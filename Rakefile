MAD_ROOT = File.dirname(__FILE__)

task :default => ["test"]

desc "Test all units and functionals"  
task :test do 
  chdir File.join(MAD_ROOT, "test") do
    sh "phpunit AllTests.php"
  end
end

desc "Print out all defined routes in match order, with names."
task :routes do
  chdir File.join(MAD_ROOT) do
    sh "php ./script/routes"
  end
end
  
namespace :test do
  desc "Run the unit tests in test/unit"
  task :units do
    chdir File.join(MAD_ROOT, "test") do
      sh "phpunit --group unit AllTests.php"
    end
  end

  desc "Run the functional tests in test/functional"
  task :functionals do
    chdir File.join(MAD_ROOT, "test") do
      sh "phpunit --group functional AllTests"
    end
  end
end
  
namespace :db do
  desc "Migrate the database through scripts in script/migrate. Target specific version with VERSION=x."
  task :migrate do 
    chdir File.join(MAD_ROOT) do
      cmd = "php ./script/db_migrate"
      cmd += " VERSION=#{ENV['VERSION']}" if ENV['VERSION']
      sh cmd
    end
  end
end