import java.util.ArrayList;
import java.util.HashSet;

public class Vertex 
{
	public int id; //unique id
	public int myTermin;
	public ArrayList<Integer> wantedTermins;
	public ArrayList<Vertex> connectedTo; //list of childs
	public boolean visited; //used in building components
	public int componentId; //component id
	public int localId; //id in component
	public int newTermin;
	
	public Vertex(int id, int myTermin, ArrayList<Integer> wantedTermins)
	{
		this.id = id;
		
		this.myTermin = myTermin;
		this.wantedTermins = wantedTermins;
		
		this.connectedTo = new ArrayList<Vertex>();
		this.visited = false;
		
		this.componentId = -1;
		this.localId = -1;
		
		this.newTermin = -1;
		
	}
	
	public String toString()
	{
		String a = String.format("Id: %d (myTermin: %d, wanted: %s) Connected to: (", this.id, this.myTermin, this.wantedTermins);
		//String a = "Id: " + this.id + " (Comp: " +this.componentId +" localId: " + this.localId + ") Connected to:";
		for(Vertex b: connectedTo)
		{
			a += " " + b.id;
		}
		a += String.format(" ) ComponentId: %d, localId: %d //new Termin: %d", this.componentId, this.localId, this.newTermin);
		//a += " //new Termin: " + this.newTermin;
		return a;
	}
	
	public static void connectPeople(ArrayList<Vertex> persons) 
	{
		for(Vertex a: persons)
		{
			a.connectedTo.clear(); //pobrisemo povezave
			for(int i = 0; i < persons.size(); i++)
			{
				if(persons.get(i).id == a.id) continue;//samega s sabo ne povezemo
				
				//pogledamo ali je list[i].ownedTermin na listi zelja trenutne osebe
				for(int wish : a.wantedTermins)
				{
					if(wish == persons.get(i).myTermin)
					{
						a.connectedTo.add(persons.get(i));
						break;
					}
				}
			}
		}
	}
	
	
	public static Vertex findVertexByID(ArrayList<Vertex> list, int id)
	{
		for (Vertex tmp : list) 
		{
			if(tmp.id == id) return tmp;
		}
		return null;
	}
	
	public static Vertex findVertexByLocalID(ArrayList<Vertex> list, int id)
	{
		for (Vertex tmp : list) 
		{
			if(tmp.localId == id) return tmp;
		}
		return null;
	}

	public static void sortInComp(ArrayList<Vertex> a, int componentId) 
	{
		int newId = 0;
		for(Vertex b : a)
		{
			b.componentId = componentId;
			b.localId = newId;
			newId++;
		}
	}
	
	public static void clean(ArrayList<Vertex> persons) 
	{
		int len1 = persons.size();
		int len2 = 0;
		
		HashSet<Integer> goodIds = new HashSet<Integer>();
		
		while(len1 != len2)
		{
			len1 = persons.size();

			//brisemo ljudi brez moznosti za zamenjavo
			for(int i = persons.size()-1; i >= 0; i--)
			{
				if(persons.get(i).connectedTo.size() == 0)
				{
					persons.remove(i);
				}
			}
			
			//brisemo ljudi, katerih termine ne zeli nihce
			goodIds.clear();
			
			for(Vertex tmp : persons)
			{
				for(int i = 0; i < tmp.connectedTo.size(); i++)
				{
					goodIds.add(tmp.connectedTo.get(i).id);
				}
			}
			
			for(int i = persons.size()-1; i >= 0; i--)
			{
				if(!goodIds.contains(persons.get(i).id))
				{
					persons.remove(i);
				}
			}

			
			connectPeople(persons);
			len2 = persons.size();
		}
	}

	public static double[][] getMatrix(ArrayList<Vertex> a) 
	{
		int n = a.size();
		double[][] matrix = new double[n][n];
		
		for(int i = 0; i < n; i++)
		{
			for(int j = 0; j < n; j++)
			{
				if(i == j) matrix[i][i] = 1;
				else matrix[i][j] = n+1;
			}
		}
		
		for(Vertex b : a)
		{
			for(Vertex child : b.connectedTo) matrix[b.localId][child.localId] = 0;
		}
		//echo
		/*for(int i = 0; i < matrix.length; i++)
		{
			for(int j = 0; j < matrix.length; j++)
			{
				System.out.print((int)matrix[i][j] +" ");
			}
			System.out.println();
		}*/
		return matrix;
	}
}
