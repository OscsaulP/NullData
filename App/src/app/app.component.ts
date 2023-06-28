import { AfterViewInit, Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';

interface Empleado {
  nombre: string;
  email: string;
  puesto: string;
  fechaNacimiento: string;
  domicilio: string;
  skills: Skill[];
}

interface Skill {
  nombre: string;
  calificacion: number;
}

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit,AfterViewInit  {

  empleados: Empleado[] = [];
  empleadoSeleccionado: Empleado | null = null;
  googleMapsApiKey: string = 'TU_CLAVE_DE_API';

  constructor(private http: HttpClient) { }

  ngOnInit() {
    this.obtenerEmpleados();
  }

  ngAfterViewInit() {
    this.initMap();
  }

  obtenerEmpleados() {
    this.http.get<Empleado[]>('http://localhost:8000/api/empleados')
      .subscribe(data => {
        this.empleados = data;
      });
  }

  registrarEmpleado(empleado: Empleado) {
    this.http.post('http://localhost:8000/api/empleados', empleado)
      .subscribe(() => {
        this.obtenerEmpleados();
      });
  }

  consultarEmpleado(empleado: Empleado) {
    this.http.get<Empleado>(`http://localhost:8000/api/empleados/${empleado.email}`)
      .subscribe(data => {
        this.empleadoSeleccionado = data;
      });
  }

  actualizarEmpleado(empleado: Empleado) {
    this.http.put(`http://localhost:8000/api/empleados/${empleado.email}`, empleado)
      .subscribe(() => {
        this.obtenerEmpleados();
      });
  }

  initMap() {
    const mapElement = document.getElementById('map');

    if (mapElement) {
      const map = new google.maps.Map(mapElement, {
        center: { lat: 0, lng: 0 },
        zoom: 12
      });

      // Aquí puedes agregar marcadores o realizar otras operaciones con el mapa
    }
  }
  
}


  
