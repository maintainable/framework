require 'yaml'

# capture arguments after task name
args = ARGV.dup
args.shift
args = args.join(' ')

# export list of tasks from mad
chdir File.dirname(__FILE__) # MAD_ROOT
task_runner = File.join('.', 'script', 'task')
tasks = YAML.load(`php #{task_runner} --yaml`)

# dynamically build rakefile
code = ""
tasks.each_pair do |name, desc|
  namespaces = name.split(':')
  task  = namespaces.pop

  namespaces.each { |n| code << "namespace :#{n} do \n" }
  code << "desc \"#{desc.gsub('"', '\\"').strip}\"
           task :#{task} do 
             sh('php #{task_runner} #{name} #{args}')
           end\n"
  namespaces.each { |n| code << "end\n" }
end
eval code

task :default => ["test"]
