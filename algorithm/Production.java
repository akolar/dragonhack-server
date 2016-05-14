import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedList;
import java.util.Queue;

public class Production {

	public static void main(String[] args) 
	{
		//System.out.println(Arrays.toString(args));
		ArrayList<Vertex> persons = readData(args);
		
		Vertex.connectPeople(persons); //create digraph on termins
		Vertex.clean(persons); //remove people with no chances for success
		
		//for(Vertex a : persons) System.out.println(a.toString()); //echo cleared input
		
		ArrayList<ArrayList<Vertex>> components = splitToComponents(persons); //split digraphg on connected components
		
		//System.out.println("---------------------------------------------");
		//System.out.println("Number of connected components: " + components.size());
		
		String output = "";
		int componentId = 0;
		for(ArrayList<Vertex> a : components) //do for every component
		{
			Vertex.sortInComp(a, componentId); //add local ids in this component	
			
			//System.out.println("---------------------------------------------");
			//System.out.println("Number of vertex in this component: "+ a.size());

			int[] result = new HungarianAlgorithm(Vertex.getMatrix(a)).execute();
			
			//int success = 0;
	        for(int i = 0; i < result.length; i++) //echo results
	        {
	        	//we need to convert from local ids to global ones
	        	Vertex v = Vertex.findVertexByLocalID(a, i);
	        	Vertex u = Vertex.findVertexByLocalID(a, result[i]);
	        	
	        	v.newTermin = u.myTermin;
	        	
	        	int fromId = v.id;
	        	int toId = u.id;
	        	
	        	if(fromId != toId)
	        	{
	        		output += fromId + " " + v.newTermin + " ";
	        		//System.out.println(fromId + " -> " + toId);
	        		//output += fromId +"  "+ toId;
	        		//System.out.println(fromId);
	        		//System.out.println(toId);
	        		//success++;
	        	}
	        }
			//System.out.println("Success: " + success );
			componentId++;
		}
		output = output.trim();
		System.out.println(output);
		//for(Vertex a : persons) System.out.println(a.toString()); //echo cleared input
	}
	
	
	private static ArrayList<Vertex> readData(String[] args) 
	{
		int stPeople = Integer.parseInt(args[0]);
		
		ArrayList<Vertex> persons = new ArrayList<Vertex>();
		
		int counter = 1;
		while(counter < args.length)
		{
			int index = Integer.parseInt(args[counter]);
			int owned = Integer.parseInt(args[counter+1]);
			int stWishes = Integer.parseInt(args[counter+2]);

			ArrayList<Integer> wishes = new ArrayList<Integer>();
			
			for(int j = 0; j < stWishes; j++)
			{
				wishes.add(Integer.parseInt(args[counter+3+j]));
			}
			persons.add(new Vertex(index, owned, wishes));
			//System.out.println(persons.get(persons.size()-1));
			counter = counter + 3 + stWishes;
		}
		
		return persons;
	}

	private static ArrayList<ArrayList<Vertex>> splitToComponents(ArrayList<Vertex> persons)
	{
		ArrayList<ArrayList<Vertex>> components = new ArrayList<ArrayList<Vertex>>();

		int StObdelanih = 0;
		Queue<Vertex> queue = new LinkedList<Vertex>();
		while(StObdelanih < persons.size())
		{
			ArrayList<Vertex> current = new ArrayList<Vertex>();
			queue.clear();
			
			for(Vertex a : persons) //najdemo prvega nepovezanega
			{
				if(!a.visited)
				{
					queue.add(a);
					break;
				}
			}
			
			while(!queue.isEmpty()) //prehodimo komponento
			{
				Vertex element = queue.poll();
				element.visited = true;
				StObdelanih++;
				current.add(element);
				
				for(Vertex child : element.connectedTo)
				{
					if(!child.visited && !queue.contains(child)) queue.add(child);
				}
			}
			components.add(current);
		}

		return components;
	}
}
